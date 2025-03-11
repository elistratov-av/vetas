<?php


namespace app\modules\foundPet\commands;

use app\models\db\found_pet\Ad;
use app\modules\foundPet\etp\status\Status1050;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Помощь в тестировании foundPet
 * @package app\modules\foundPet\commands
 */
class AdTestController extends \yii\console\Controller
{

    /**
     * Вывод текущих настроек
     */
    public function actionShowConfig()
    {
        $module = \Yii::$app->getModule('foundPet');
        var_dump($module->params);
    }

    /**
     * Выводит пример curl для получения токена (по текущим настройкам конфига)
     */
    public function actionCurlTemplateAuthorize()
    {
        $module = \Yii::$app->getModule('foundPet');

        $example = [
            'curl -vvv -k -d "grant_type=client_credentials" -H',
            escapeshellarg(
                'Authorization: Basic ' . base64_encode($module->params['consumerKey'] . ':' . $module->params['consumerSecret'])
            ),
            escapeshellarg($module->params['tokenUrl']),
        ];

        $this->stdout(PHP_EOL . PHP_EOL);
        $this->stdout(implode(
            ' ', $example
        ));
        $this->stdout(PHP_EOL . PHP_EOL);
    }

    /**
     * Формирует пример curl запроса на 1050 для указанного объявления
     * @param $id
     */
    public function actionCurlTemplate1050($id)
    {
        $data = $this->getRequest($id);
        $module = \Yii::$app->getModule('foundPet');
        $sendUrl = ArrayHelper::getValue($module->params, 'sendUrl');


        $example = [
            'curl -vvv -H',
            escapeshellarg(
                'Content-Type: application/json; charset=utf-8'
            ),
            '-H',
            escapeshellarg(
                'Accept: application/json'
            ),
            '-H',
            escapeshellarg(
                'Authorization: Bearer '
            ),
            '-d',
            escapeshellarg(
                Json::encode($data)
            ),
            $sendUrl
        ];

        $this->stdout(PHP_EOL . PHP_EOL);
        $this->stdout(implode(
            ' ', $example
        ));
        $this->stdout(PHP_EOL . PHP_EOL);

    }

    /**
     * @param $id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getRequest($id)
    {
        $ad = Ad::findOne(['id' => $id]);

        if (empty($ad)) {
            $this->stderr(PHP_EOL . 'Ad not found' . PHP_EOL);
            die();
        }
        /** @var Status1050 $status */
        $status = $status = \Yii::createObject([
            'class' => Status1050::class,
            'ad' => $ad,
            'ServiceNumber' => (isset($ad) ? $ad->service_number : (isset($requestData['service_number']) ? $requestData['service_number'] : null)),
        ]);


        return $status->prepareRequestData();
    }

}