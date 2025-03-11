<?php

namespace app\modules\foundPet\commands;

use app\models\db\found_pet\Ad;
use app\modules\foundPet\Module;
use app\modules\foundPet\queue\JobHandler;
use DateTimeImmutable;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;


class AdController extends Controller
{
    private $connectionId;

    /**
     * Сопоставление ключей подписки и наших аттрибутов БД (значения)
     */
    private CONST SUBSCRIPTIONS_FIELD_NORMALIZATION = ['species' => 'id_species', 'breed' => 'id_breed', 'sex' => 'sex', 'age' => 'age', 'color' => 'id_color'];

    /**
     * Автоматическое закрытие объявление по дате
     *
     * @param string $date Модификатор даты. Можно использовать для смещения
     * @return int
     */
    public function actionAutoClose($date = 'yesterday'): int
    {
        try {
            $dt = new DateTimeImmutable($date);
        } catch (\Throwable $e) {
            $this->stderr(sprintf('Invalid time "%s"', Console::ansiFormat($date, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }
        \Yii::info(sprintf('[connection-id: %s][class: %s] Start auto close ads...', $this->getConnectionId(), static::class), Module::LOG_CATEGORY);
        $records = [];
        $q = Ad::find()->where([
            'and',
            ['is_active' => true],
            ['<=', 'active_till', $dt->format('Y-m-d')],
        ]);
        /** @var Ad $ad */
        foreach ($q->each() as $ad) {
            try {
                if (!$this->close($ad)) {
                    \Yii::error(sprintf('Error during close "%s" ad. Errors: %s', $ad->id, Console::errorSummary($ad)), Module::LOG_CATEGORY);

                    continue;
                }
                $records[] = "{$ad->id}({$ad->service_number})";
            } catch (\Throwable $e) {
                $this->logException($e);
            }
        }
        \Yii::info(sprintf(
            '[connection-id: %s][class: %s] Complete auto close ads. %s',
            $this->getConnectionId(),
            static::class,
            count($records) ? 'Precessed: ' . implode(', ', $records) : 'No ads found'
        ), Module::LOG_CATEGORY);

        return ExitCode::OK;
    }

    /**
     * Автозакрытие объявления по указанному id
     *
     * @param string|int $id Id объявления
     * @return int
     */
    public function actionAutoCloseById($id): int
    {
        $ad = Ad::findOne(['id' => $id]);
        if (!$ad) {
            $this->stderr(sprintf('Ad with id "%s" not found', Console::ansiFormat($id, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        if (!$this->close($ad)) {
            $this->stderr(sprintf("Error during close \"%s\" ad. Errors:\n%s", Console::ansiFormat($id, [Console::FG_RED]), Console::errorSummary($ad)) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        $this->stderr(sprintf('Ad "%s" successful closed', Console::ansiFormat($id, [Console::FG_GREEN])) . PHP_EOL);

        return ExitCode::OK;
    }

    /**
     * Отправка 8021.2 статуса для объявлений с подпиской
     *
     * @param string $date Модификатор даты. Можно использовать для смещения
     * @return int
     */
    public function actionNotifySubscribed($date = '+5 days'): int
    {
        try {
            $dt = new DateTimeImmutable($date);
        } catch (\Throwable $e) {
            $this->stderr(sprintf('Invalid time "%s"', Console::ansiFormat($date, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }
        \Yii::info(sprintf('[connection-id: %s][class: %s] Start sent 8021.2 notification for ads created in "%s"...', $this->getConnectionId(), static::class, $dt->format('Y-m-d')), Module::LOG_CATEGORY);
        $records = [];
        $q = Ad::find()->where([
            'and',
            ['is_active' => true],
            ['=', 'active_till', $dt->format('Y-m-d')],
            ['is not', 'subscriptions', null],
        ]);
        /** @var Ad $ad */
        foreach ($q->each() as $ad) {
            try {
                $this->getJobHandler()->sendStatus80212($ad);
                $records[] = "{$ad->id}({$ad->service_number})";
            } catch (\Throwable $e) {
                $this->logException($e);
            }
        }

        \Yii::info(sprintf(
            '[connection-id: %s][class: %s] Complete sent 8021.2 notification for ads. %s',
            $this->getConnectionId(),
            static::class,
            count($records) ? 'Precessed: ' . implode(', ', $records) : 'No ads found'
        ), Module::LOG_CATEGORY);

        return ExitCode::OK;
    }

    /**
     * Отправка 8021.1 статуса для объявлений с подпиской (совпадения для подписантов)
     * Ищем подписантов, которых могут заинтересовать новые сообщений
     * Если в необработанных пользователь сам подписан - нам все равно,
     * тк его интересуют только те объявленния, что появяться после подписки
     *
     * @return int
     */
    public function actionNotify(): int
    {
        $q = Ad::find()->where([
            'is_active' => true,
            'processed' => false,
        ]);
        /** @var Ad $ad */
        foreach ($q->each() as $ad) {
            $records = $this->findAndNotifyInterestedSubscribers($ad);

            $ad->processed = true;
            $ad->save(false, ['processed']);

            \Yii::info(sprintf(
                '[connection-id: %s][class: %s] Complete sent 8021.1 notification for ad %s. %s',
                $this->getConnectionId(),
                static::class,
                "{$ad->id}({$ad->service_number})",
                count($records) ? 'Passed: ' . implode(', ', $records) : 'No ads found'
            ), Module::LOG_CATEGORY);
        }

        return ExitCode::OK;
    }

    /**
     * Поиск заинтересованных подписчиков и оповещение,
     * если совпали значения нового объявления и объявления подписанта
     * Сравниваются только те поля, на которые подписался заинтересованный подписчик
     *
     * @param Ad $unprocessed_ad
     * @return string[]
     */
    protected function findAndNotifyInterestedSubscribers($unprocessed_ad)
    {
        // Активные подписчики
        $outwards = Ad::find()->where([
            'AND',
            ['is_active' => true],
            ['type' => $unprocessed_ad->getOutwardType()],
            ['IS NOT', 'subscriptions', null],
        ]);
        $records = [];
        /** @var Ad $outward */
        foreach ($outwards->each() as $outward) {

            if ($this->compareSubscriptionAds($unprocessed_ad, $outward) !== true){
                continue;
            }

            try {
                $this->getJobHandler()->sendStatus80211($outward, $unprocessed_ad);
                $records[] = "{$outward->id}({$outward->service_number})";
            } catch (\Throwable $e) {
                $this->logException($e);
            }
        }

        return $records;
    }

    /**
     * Сравниваем два объявлениям по их полям.
     * В сравнении участвуют только те, что указаны в $outward->subscriptions
     *
     * @param Ad $unprocessed_ad
     * @param Ad $outward
     * @param bool $debug
     * @return bool
     */
    protected function compareSubscriptionAds($unprocessed_ad, $outward, $debug = false)
    {
        foreach (self::SUBSCRIPTIONS_FIELD_NORMALIZATION as $type => $attr) {
            $by = $outward->subscriptions[$type] ?? false;

            if ($by
                && $unprocessed_ad->getAttribute($attr)
                && $unprocessed_ad->getAttribute($attr) !== $outward->getAttribute($attr)
            ) {
                if ($debug){
                    $this->stdout("Negative by compare {$attr}: {$unprocessed_ad->getAttribute($attr)} {$outward->getAttribute($attr)}".PHP_EOL);
                }
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Тестирование сверки двух объявлений (id объявления, id объявления c подпиской)
     * @param $id_ad
     * @param $id_ad_subscriber
     */
    public function actionTestCompareAds($id_ad, $id_ad_subscriber)
    {
        $unprocessed_ad = Ad::findOne(['id' => $id_ad]);
        $outward = Ad::findOne(['id' => $id_ad_subscriber]);

        if (empty($unprocessed_ad)){
            $this->stderr(sprintf('Ad with id "%s" not found', Console::ansiFormat($id_ad, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        if (empty($outward)){
            $this->stderr(sprintf('Ad with id "%s" not found', Console::ansiFormat($id_ad_subscriber, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        if (empty($outward->subscriptions)){
            $this->stderr(sprintf('Ad with id "%s" without SUBSCRIPTIONS', Console::ansiFormat($id_ad_subscriber, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }
        var_dump('UNPROCESSED');
        var_dump($unprocessed_ad->getAttributes(self::SUBSCRIPTIONS_FIELD_NORMALIZATION));
        var_dump('SUBSCRIBER');
        var_dump($outward->getAttributes(self::SUBSCRIPTIONS_FIELD_NORMALIZATION));
        var_dump($outward->subscriptions);

        $result = $this->compareSubscriptionAds($unprocessed_ad, $outward, true);
        var_dump('RESULT');
        var_dump($result);
    }

    /**
     * Хелпер для поиска объявлений по ЕНО
     *
     * @param string|int $sid Единый номер обращения (ЕНО)
     * @return int
     */
    public function actionSearchByServiceNumber($sid): int
    {
        $q = Ad::find()->where(['like', 'service_number', $sid])->orderBy(['id' => SORT_ASC]);

        if (!$q->count()) {
            $this->stderr(sprintf('No one ad found for service id "%s"', Console::ansiFormat($sid, [Console::FG_RED])) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        /** @var Ad $ad */
        foreach ($q->each() as $ad) {
            $this->stdout(sprintf('Found ad with id "%s" and service id is "%s"', Console::ansiFormat($ad->id, [Console::FG_GREEN]), Console::ansiFormat($ad->service_number, [Console::FG_GREEN])) . PHP_EOL);
        }

        return ExitCode::OK;
    }

    /**
     * Закрытие объявления. Отправка статуса.
     *
     * @param Ad $ad
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    private function close(Ad $ad): bool
    {
        $ad->is_active = false;
        $ad->active_till = null;
        $ad->closed_at = date('Y-m-d H:i:s');
        $ad->closed_reason = 'Истек срок размещения объявления';
        $ad->closed_by = Ad::CLOSED_AUTO;

        if (!$ad->save(true, ['is_active', 'active_till', 'closed_at', 'closed_reason', 'closed_by', 'updated_at'])) {
            return false;
        }

        $this->getJobHandler()->adAutoCloseSuccess($ad);

        return true;
    }

    private function getConnectionId(): string
    {
        if (!$this->connectionId) {
            $this->connectionId = str_replace('.', '', uniqid('', true));
        }

        return $this->connectionId;
    }

    private function getJobHandler(): JobHandler
    {
        return new JobHandler();
    }

    private function logException(\Throwable $e): void
    {
        \Yii::error(sprintf('[connection-id: %s][class: %s] %s', $this->getConnectionId(), self::class, $e->getMessage()), Module::LOG_CATEGORY);
        \Yii::debug(sprintf('[connection-id: %s][class: %s] %s', $this->getConnectionId(), self::class, (string)$e), Module::LOG_CATEGORY);
    }
}
