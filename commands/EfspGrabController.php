<?php


namespace app\commands;

use app\models\db\efsp\FiasData;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Закачка и перенос в нашу БД данных из ЕФСП (адреса)
 * @package app\commands
 */
class EfspGrabController extends \yii\console\Controller
{
    /**
     * Я не знаю сколько живет токен потому будем его иногда обновлять
     */
    const COUNT_REQUESTS_BEFORE_UPDATE_TOKEN = 1000;
    /**
     * @var int Счетчик запросов (может обнуляться)
     */
    protected $request_counter = 0;

    /**
     * @var \app\common\efsp\Client
     */
    protected $efsp_client;

    public function init()
    {
        parent::init();
        $this->efsp_client = \Yii::$app->authClients->getClient('efsp');
    }

    /**
     * Два-в-одном: Качаем данные из ЕФСП и обновляем fias_addresses.bti_city_area_code
     * @param false $limit
     * @throws \yii\db\Exception
     */
    public function actionGrubFiasDataAndUpdFiasAddressesByHouseguid($limit = false)
    {
        $this->actionGrubFiasDataByHouseguid($limit);
        $this->actionUpdFiasAddressesByHouseguid();
    }

    /**
     * Выбирает те public.fias_addresses.houseguid у которых НЕ проставлены bti_city_area_code в public.fias_addresses И ОТСУТСТВУЮТ В efsp.fias_data и пополняет ими efsp.fias_data
     * @param false $limit
     * @throws \Exception
     */
    public function actionGrubFiasDataByHouseguid($limit = false)
    {
        $query = $this->getQueryUncomplitedFiasAddressesByHouseguid();

        try {
            $this->efsp_client->authenticateClient();
        } catch (\Exception $exception) {
            $this->errorMessage($exception->getMessage());
            return;
        }

        $this->grabLoop(
            '/getAddress?fiasId=',
            $query,
            $limit
        );
    }

    /**
     * Проставляет fias_addresses.bti_city_area_code по данным из efsp.fias_data
     * @throws \yii\db\Exception
     */
    public function actionUpdFiasAddressesByHouseguid()
    {
        $this->infoMessage('Обновляем fias_addresses.bti_city_area_code по данным из efsp.fias_data');

        $sql=<<<SQL
with t as (
    SELECT DISTINCT
           efsp.fias_data.fias_uid,
           ARRAY(SELECT jsonb_array_elements_text(efsp.fias_data.result -> 'suggestions' -> 0 -> 'data' -> 'bti_city_area_code')) as bti
    FROM public.fias_addresses
             INNER JOIN efsp.fias_data ON fias_addresses.houseguid = efsp.fias_data.fias_uid
    WHERE
          fias_addresses.bti_city_area_code IS NULL
          AND
          efsp.fias_data.result -> 'suggestions' -> 0 -> 'data' -> 'bti_city_area_code' IS NOT NULL
)
UPDATE public.fias_addresses
SET bti_city_area_code = t.bti
FROM t
WHERE t.fias_uid = fias_addresses.houseguid;
SQL;
        $count = \Yii::$app->db
            ->createCommand($sql)
            ->execute();

        $this->infoMessage('Обновлено в процессе переноса адресов: ' . $count);
    }

    /**
     * @param $api_url
     * @param Query $query
     * @param $limit
     * @throws \Exception
     */
    protected function grabLoop($api_url, $query, $limit)
    {
        $query_count = (clone $query);
        $this->infoMessage('Необработанных fias UID: ' . $query_count->count());

        // LIMIT
        if (is_numeric($limit)) {
            $this->infoMessage('SET LIMIT ' . $limit);
            $query->limit($limit);
        }

        $count_total = 0;
        $progress_counter = 0;
        $progress_counter_limit = 100;
        foreach ($query->each() as $row) {
            /*
             * Grub and store
             */
            $this->_grabFromApiAndStore($api_url, $row['uid']);
            /*
             * Info
             */
            if ($progress_counter >= $progress_counter_limit) {
                $progress_counter = 0;
                $this->infoMessage( ($count_total - $progress_counter_limit) . ' - ' . $count_total);
            }
            $count_total++;
            $progress_counter++;
        }

    }

    protected function _grabFromApiAndStore($api_url, $fias_uid)
    {
        /*
         *  Update auth if need
         */
        if ($this->request_counter > self::COUNT_REQUESTS_BEFORE_UPDATE_TOKEN) {
            $this->request_counter = 0;
            $this->efsp_client->authenticateClient();
            $this->infoMessage('Обновляем access токен к ефсп');
        }
        $url = $api_url . $fias_uid;

        try {
            $result = $this->efsp_client->api($url);
            $this->request_counter++;
        } catch (\Exception $exception) {
            $this->errorMessage($exception->getMessage());
            $this->errorMessage($url);
            return;
        }


        $new_data = new FiasData([
            'fias_uid' => $fias_uid,
            'api_url' => $this->efsp_client->apiBaseUrl . $api_url,
            'result' => $result,
        ]);

        if (!$new_data->save()) {
            $errors = $new_data->getErrorSummary(true);
            throw new \Exception(empty($errors) ? 'Ошибка при сохранении адреса' : implode("\n", array_values($errors)));
        }

    }

    protected function getQueryUncomplitedFiasAddressesByHouseguid()
    {
        return (new Query())
            ->select('houseguid AS uid')
            ->distinct()
            ->from('fias_addresses')
            ->leftJoin('efsp.fias_data', 'fias_addresses.houseguid = efsp.fias_data.fias_uid')
            ->where([
                'AND',
                ['IS NOT', 'houseguid', null],
                ['IS', 'bti_city_area_code', null],
                ['IS', 'efsp.fias_data.id', null]
            ]);
    }

    protected function errorMessage($msg)
    {
        Console::output();
        Console::output(Console::ansiFormat($msg, [Console::FG_RED]));
    }
    protected function infoMessage($msg)
    {
        Console::output();
        Console::output(Console::ansiFormat($msg, [Console::FG_YELLOW]));
    }
}