<?php

namespace app\modules\soap\v2\queue;

use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\Visits;
use app\modules\soap\Module;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\v2\models\etp\ApplicationMessage;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ETPVisitStatusSender
 * @package app\modules\soap\v2\queue
 */
class ETPVisitStatusSender
{
    /**
     * @var \app\modules\soap\v2\models\db\ETPMessage
     */
    protected $etpMessage;
    /**
     * @var \app\modules\soap\v2\models\etp\ApplicationMessage
     */
    protected $message;
    /**
     * @var Visits
     */
    protected $visit;
    /**
     * @var string
     */
    protected $sendUrl;
    /**
     * @var string
     */
    protected $authToken;

    /**
     * ETPVisitStatusSender constructor.
     * @param int|null    $visit_id
     * @param string|null $service_number
     * @throws \Exception
     */
    public function __construct(int $visit_id = null, string $service_number = null)
    {
        if (empty($visit_id) && empty($service_number)) {
            throw new InvalidConfigException('Either visit_id or service_number should be specified');
        }

        if (!empty($visit_id)) {
            if (!$this->visit = Visits::findOne(['id' => $visit_id])) {
                throw new \Exception("Не найден прием #{$visit_id}");
            }

            if (!$this->etpMessage = ETPMessage::findOne(['visit_id' => $visit_id])) {
                throw new \Exception("Не найдена заявка из ЕТП привязанная к приему #{$visit_id} (v2)");
            }
        } else {
            if (!$this->etpMessage = ETPMessage::findOne(['service_number' => $service_number])) {
                throw new \Exception("Не найдена заявка из ЕТП по ЕНО #{$service_number} (v2)");
            }
        }

        $this->message = new ApplicationMessage(['requestData' => $this->etpMessage->message]);

        $this->prepareSender();
    }

    /**
     * @return string
     */
    public function getServiceNumber(): string
    {
        return $this->etpMessage->service_number;
    }

    /**
     * @return Visits|null|static
     */
    public function getVisit()
    {
        return $this->visit;
    }

    /**
     * @param \app\modules\soap\models\etp\status\StatusInterface $status
     * @param string|null                                         $status_id
     * @throws \yii\base\Exception
     */
    public function sendStatus(StatusInterface $status, ?string $status_id)
    {
        if (empty($this->sendUrl)) {
            return;
        }
        $connectionId = str_replace('.', '', uniqid('', true));
        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'Accept: text/xml',
        ];
        $soapHeaders = [];
        if ($this->etpMessage->system_id && $this->etpMessage->message_id) {
            $soapHeaders['SystemId'] = $this->etpMessage->system_id;
            $soapHeaders['MessageId'] = $this->etpMessage->message_id;
        }
        $request = $this->message->makeResponseData($status, $status_id, $soapHeaders);

        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Start sent request to '%s'\nHeaders:\n%s\nBody:\n%s",
            $connectionId,
            self::class,
            $this->sendUrl,
            Json::encode($headers),
            trim($request) ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);

        // Hide auth from logs
        if (!empty($this->authToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }

        $output = [];
        $ch = curl_init($this->sendUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, static function($resource, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if ($header !== '') {
                $output[] = $header;
            }
            return mb_strlen($headerString, '8bit');
        });

        $response = curl_exec($ch);
        curl_close($ch);

        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Finish sent request to '%s'\nHeaders:\n%s\nBody:\n%s",
            $connectionId,
            self::class,
            $this->sendUrl,
            Json::encode($output),
            trim((string)$response) ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);
    }

    /**
     * @return void
     */
    private function prepareSender()
    {
        $module = \Yii::$app->getModule('soap');
        $this->sendUrl = ArrayHelper::getValue($module->params, 'sendUrlV2');

        if (isset($module->params['authTokenV2'])) {
            $this->authToken = $module->params['authTokenV2'];
        } elseif (isset($module->params['tokenUrlV2']) && isset($module->params['consumerKeyV2']) && isset($module->params['consumerSecretV2'])) {
            try {
                $ch = curl_init($module->params['tokenUrlV2'] . '?grant_type=client_credentials');
                $headers = [
                    'Authorization: Basic ' . base64_encode($module->params['consumerKeyV2'] . ':' . $module->params['consumerSecretV2']),
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                if ($response === false) {
                    \Yii::error('Failed to retrieve auth token: ' . curl_error($ch), Module::LOG_CATEGORY);
                } elseif (empty($response)) {
                    \Yii::error('Failed to retrieve auth token: empty response', Module::LOG_CATEGORY);
                } else {
                    $arr = Json::decode($response);
                    if (isset($arr['access_token'])) {
                        $this->authToken = $arr['access_token'];
                    } else {
                        \Yii::error('Failed to retrieve auth token: unexpected response format', Module::LOG_CATEGORY);
                    }
                }
                curl_close($ch);
            } catch (\Throwable $e) {
                \Yii::error('Failed to retrieve auth token: ' . $e->getMessage(), Module::LOG_CATEGORY);
            }
        }
    }
}
