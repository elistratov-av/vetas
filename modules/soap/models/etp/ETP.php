<?php

namespace app\modules\soap\models\etp;

use app\common\soap\MessageParser;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\Module;
use yii\base\Component;

/**
 * Class ETP
 * @package app\modules\soap\models\etp
 */
class ETP extends Component
{
    const
        SEX_ANIMAL_EMPTY = 0,
        SEX_ANIMAL_MALE = 1,
        SEX_ANIMAL_FEMALE = 2
    ;

    /**
     * Путь до схемы xsd
     *
     * @var string
     */
    public $schemePath;

    /**
     * URL для сервиса листенера очереди
     * @var string
     */
    public $sendUrl;

    /**
     * Обработка запроса от ЕТП
     * Принимает xml сообщение, для записи на прием (CoordinateMessage) или изменения статуса (CoordinateStatusMessage)
     * @param string $xml
     */
    public function handleRequest(string $xml)
    {
        try {
            /** @var CoordinateMessageInterface $message */
            $message = \Yii::$container->get('coordinateMessage', [], [
                'message' => MessageParser::xmlAsArray($xml)
            ]);

            $this->validateXML($xml);

            $message->process();
        } catch (\Exception $e) {
            $error = $e->getMessage() . "\n\r" . $e->getTraceAsString();
            $message->sendErrorMessage($error);
        }
    }

    /**
     * @param string $xml
     *
     * @throws ETPException
     */
    protected function validateXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        try {
            $valid = $dom->schemaValidate(\Yii::getAlias($this->schemePath));
        } catch (\Exception $e) {
            throw new ETPException('Передаваемые параметры в xml не соответствуют схеме: ' . $e->getMessage(), 400);
        }

        if (!$valid) {
            throw new ETPException('Передан невалидный xml', 400);
        }
    }

    /**
     * @param StatusInterface $status
     * @param CoordinateMessageInterface $message
     */
    public function sendStatusMessage(StatusInterface $status, CoordinateMessageInterface $message)
    {
        $request = $message->makeResponseData($status);
        $connectionId = str_replace('.', '', uniqid('', true));

        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Start sent request to '%s'\nBody:\n%s",
            $connectionId,
            static::class,
            $this->sendUrl,
            trim($request) ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);

        $ch = curl_init($this->sendUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Finish sent request to '%s'\nBody:\n%s",
            $connectionId,
            static::class,
            $this->sendUrl,
            trim((string)$response) ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);
    }
}
