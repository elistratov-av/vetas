<?php

namespace app\common\components\inform\jobs;

use app\common\components\inform\SpkService;
use app\models\db\subscription\Subscriptions;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;

class UnsubscribeJob extends BaseObject implements JobInterface
{
    /** @var integer */
    public $contact_id;

    /** @var string */
    public $contact;

    /** @var string */
    public $type;

    /** @var string */
    public $sso_id;

    /** @var bool */
    public $delete = false;

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     * @throws \Throwable
     */
    public function execute($queue)
    {
        try {
            Console::stdout(Console::ansiFormat("Удаление подписки для контакта #{$this->contact}" . PHP_EOL, [Console::FG_GREEN]));

            /** @var SpkService $service */
            $service = \Yii::$app->spkService;
            Console::stdout(Console::ansiFormat("Проверяем наличие подписки в ИС ПК" . PHP_EOL, [Console::FG_YELLOW]));
            if ($this->type == 'msisdn') {
                $this->contact = trim($this->contact, '+');
            }
            $subscriptions = $service->getSubscriptionsForContact($this->type, $this->contact, $this->sso_id);

            if (!empty($subscriptions)) {
                foreach ($subscriptions as $subscription) {
                    Console::stdout(Console::ansiFormat("Удаление подписки {$this->contact} в ИС ПК" . PHP_EOL, [Console::FG_YELLOW]));
                    $service->deleteSubscription($subscription->id);
                }
            } else {
                Console::stdout(Console::ansiFormat("Не найдено подписок в ИС ПК для {$this->contact}" . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            }

            if ($subscription = Subscriptions::findOne(['id_contact' => $this->contact_id])) {
                if ($this->delete) {
                    $subscription->delete();
                } else {
                    $subscription->subscribed = false;
                    if (!$subscription->id_contact) {
                        $subscription->delete();
                    } else {
                        $subscription->save();
                    }
                }
            } else {
                Console::stdout(Console::ansiFormat("Не найдена пописка для id_contact = {$this->contact_id}" . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            }
        } catch (\Exception $e) {
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
