<?php

namespace app\modules\foundPet\queue;

use app\models\db\found_pet\MessageSent;
use app\modules\foundPet\models\CurlRequest;
use app\modules\foundPet\Module;
use yii\base\InvalidConfigException;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class StatusSender
 * @package app\modules\foundPet\queue
 */
class StatusSender
{
    /**
     * @var string
     */
    protected $sendUrl;

    /**
     * @var string
     */
    protected $authToken;

    /**
     * @var string
     */
    protected $serviceNumber;

    /**
     * StatusSender constructor.
     */
    public function __construct($serviceNumber)
    {
        $this->serviceNumber = $serviceNumber;
        $this->prepareSender();
    }

    /**
     * @param string $statusCode
     * @param array|string $data
     */
    public function sendStatus($statusCode, $data)
    {
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->authToken,
        ];

        $request = is_array($data) ? Json::encode($data) : $data;
        $request = new CurlRequest(
            $this->sendUrl,
            $headers,
            $request,
            $this->serviceNumber,
            $statusCode
        );

        $request->exec();
        $this->log($request);
    }

    /**
     * Подгружаем параметры, получаем токен авторизации
     * @return void
     * @throws InvalidConfigException|Exception
     */
    private function prepareSender()
    {
        $module = \Yii::$app->getModule('foundPet');
        $this->sendUrl = ArrayHelper::getValue($module->params, 'sendUrl');

        if (isset($module->params['authToken'])) {
            $this->authToken = $module->params['authToken'];
            return;
        }
        if (!isset($module->params['tokenUrl']) || !isset($module->params['consumerKey']) || !isset($module->params['consumerSecret'])) {
            throw new InvalidConfigException('check tokenUrl, consumerKey, consumerSecret keys in config');
        }


        $url = $module->params['tokenUrl'] . '?grant_type=client_credentials';
        $headers = [
            'Authorization: Basic ' . base64_encode($module->params['consumerKey'] . ':' . $module->params['consumerSecret']),
        ];

        $request = new CurlRequest(
            $url,
            $headers,
            '',
            $this->serviceNumber,
            'auth'
        );

        $request->exec();

        if ($request->response === false) {
            $this->error($request, 'Failed to retrieve auth token');
        } elseif (empty($request->response)) {
            $this->error($request, 'Failed to retrieve auth token: empty response');
        } else {
            $arr = Json::decode($request->response);
            if (isset($arr['access_token'])) {
                $this->authToken = $arr['access_token'];
            } else {
                $this->error($request, 'Failed to retrieve auth token: unexpected response format');
            }
        }
    }

    /**
     * @param CurlRequest $request
     * @param string $user_error
     * @throws Exception
     */
    protected function error($request, $user_error)
    {
        $this->log($request, $user_error);
        throw new Exception($user_error);
    }


    /**
     * @param CurlRequest $request
     * @param string $user_error
     */
    protected function log($request, $user_error = '')
    {
        $data = [
            'type' => $request->exec_type,
            'service_number' => $request->exec_uid,
            'request' => $request->request,
            'response' => $request->response,
            'response_headers' => $request->response_headers,
            'response_code' => $request->response_code,
            'user_error' => $user_error,
            'curl_error' => $request->curl_error,
        ];

        $new_record = new MessageSent($data);
        $result = $new_record->save();

        // не удалось сохранить в бд - пишем хотя бы в лог
        if (!$result) {
            \Yii::info(sprintf(
                "[%s][class: %s] %s\n",
                $this->serviceNumber,
                self::class,
                Json::encode($data)
            ), Module::LOG_CATEGORY);
        }
    }
}
