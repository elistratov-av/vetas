<?php

namespace app\commands;

use yii\console\Controller;

class RabbitController extends Controller
{
    // Идентификатор события в системе ВИТАИС
    const INPUT_UUID = 'cb3111e9-8791-4e6b-9ecc-0c7370327313';
    // Идентификатор события в системе ИС ПК
    const INTERNAL_UUID = '642373e3-3444-4c86-9015-add2927a17b0';
    // Статус события, возможные значения: send, view, click, unsend, undelivered, delivered, unsubscribe
    const ACTION = 'send';
    // Канал отправки события, возможные значения: email, push, sms, viber, elk
    const STREAM_TYPE = 'push';
    // Дата и время присвоения статуса в формате unix
    const TIME = '1561649451';

    /**
     * Паблишит джоб в очередь рэббит, имитирует ответ от ИС ПК
     */
    public function actionPublishJob($inputUuid = null, $internalUuid = null, $action = null, $streamType = null, $time = null)
    {
        $producer = \Yii::$app->rabbitmq->getProducer('spk_producer');
        $msg = json_encode([
            'input_uuid' => $inputUuid ?? self::INPUT_UUID,
            'internal_uuid' => $internalUuid ?? self::INTERNAL_UUID,
            'action' => $action ?? self::ACTION,
            'stream_type' => $streamType ?? self::STREAM_TYPE,
            'time' => time(),
        ]);
        $producer->publish($msg, 'my_exchange_name', 'YOUR_ROUTING_KEY');
    }
}
