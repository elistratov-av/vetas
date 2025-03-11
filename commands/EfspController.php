<?php

namespace app\commands;

use yii\helpers\Console;

use app\common\efsp\EfspWrapper;

/**
 * Консольная команда для работы с сервисом API ЕФСП
 */
class EfspController extends \yii\console\Controller
{
    /**
     * @var \app\common\efsp\Client
     */
    protected $client;

    public function init()
    {
        parent::init();
        // TODO:DI
        $this->client = \Yii::$app->authClients->getClient('efsp');
    }

    /**
     * Запрашивает авторизационный токен
    */
    public function actionGetToken()
    {
        $token = $this->client->authenticateClient();
        Console::output($token->getToken());
    }

    /**
     * Производит GET запрос по произвольному адресу (адресного) API, выводит данные ответа в JSON
     * 
     * Пример, запрос данных геообъекта по идентификатору ФИАС:
     * 
     * ```
     * ./yii efsp/call /getAddress?fiasId=bbabb247-ecdd-4c77-b14b-5def8ae33ccc
     * ```
     */
    public function actionCall(string $subUrl)
    {
        /* $token =  */$this->client->authenticateClient();
        $respData = $this->client->api($subUrl);

        print json_encode($respData, JSON_UNESCAPED_UNICODE);
        print PHP_EOL;
    }

    /**
     * Запрашивает координаты объекта (здания) по идентификтору ФИАС
     * 
     * Пример:
     * 
     * ```
     * ./yii efsp/get-address-coords bbabb247-ecdd-4c77-b14b-5def8ae33ccc
     * ```
     */
    public function actionGetAddressCoords(string $fiasId)
    {
        print json_encode((new EfspWrapper())->getAddressCoords($fiasId));
        print PHP_EOL;
    }
}
