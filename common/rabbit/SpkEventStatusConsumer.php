<?php

namespace app\common\rabbit;

use app\common\components\inform\events\VaccinationViolationEvent;
use app\common\components\inform\events\ViolationEvent;
use app\models\db\Violation;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use mikemadisonweb\rabbitmq\components\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\helpers\FileHelper;

/**
 * php yii rabbitmq/consume spk_event_status_consumer
 */
class SpkEventStatusConsumer implements ConsumerInterface
{
    // Возможные опции от СПК: email, push, sms, viber, elk
    const STREAM_EMAIL = 'email';
    const STREAM_PUSH = 'push';

    // Все возможные от СПК
    const ACTION_SEND = 'send';
    const ACTION_VIEW = 'view';
    const ACTION_UNSEND = 'unsend';
    const ACTION_UNDELIVERED = 'undelivered';
    const ACTION_DELIVERED = 'delivered';
    const ACTION_CLICK = 'click';
    const ACTION_UNSUBSCRIBE = 'unsubscribe';

    // Статусы которые мы считаем доставленными
    public static $deliveredActions = [ self::ACTION_VIEW, self::ACTION_DELIVERED, self::ACTION_SEND ];
    // Используемые нами типы каналов
    public static $availableStreamTypes = [ self::STREAM_EMAIL, self::STREAM_PUSH ];
    // Действия которые не пишем в статус
    public static $specialActions = [ self::ACTION_CLICK, self::ACTION_UNSUBSCRIBE ];

    // Список оповещений по нарушениям, по которым нужно оповестить в случае закрытия нарушения
    public static $violationNotificationEvents = [ ViolationEvent::EVENT_CODE, VaccinationViolationEvent::EVENT_CODE ];

    /**
     * @param AMQPMessage $msg
     * @return int|mixed
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $data = json_decode($msg->body);
            $eventId = $data->input_uuid;

            $event = Yii::$app->db->createCommand(
                'select * from subscription.log where event_id = :event_id order by id desc limit 1',
                [
                    'event_id' => $eventId,
                ]
            )->queryOne();

            if ($event
                && isset($data->action)
                && isset($data->stream_type)
                && $this->isAvailableStreamType($data->stream_type)) {

                if(!$this->isSpecialAction($data->action)) {
                    $this->updateLogStatus($event, $data->action, $data->stream_type);
                    $this->notifyIfViolationAlreadyClosed($event, $data);
                }
                if ($data->action === self::ACTION_CLICK) {
                    $this->updateClickCount($event, $data->stream_type);
                }
                if ($data->action === self::ACTION_UNSUBSCRIBE) {
                    $this->updateIsUnsubscribed($event, $data->stream_type);
                }
            } else {
                $data = $msg->body;
                \Yii::warning("Не найдено оповещения с id $eventId для полученных от rabbit-spk данных: $data", 'spk_rabbit_queue');
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            \Yii::error("SpkEventStatusConsumer упал с ошибкой: '$message'", 'spk_rabbit_queue');
        }
        if (YII_DEBUG) {
            $this->logSpkResponse($msg->body);
        }
        return ConsumerInterface::MSG_ACK;
    }

    /**
     * @param $event
     * @param $action
     * @param $streamType
     * @throws \yii\db\Exception
     */
    private function updateLogStatus($event, $action, $streamType)
    {
        $columnName = "status_".$streamType;
        Yii::$app->db->createCommand(
            "update subscription.log set ".$columnName." = :action where id = :event_id",
            [
                'event_id' => $event['id'],
                'action' => $action,
            ]
        )->execute();
    }

    /**
     * @param $event
     * @param $streamType
     * @throws \yii\db\Exception
     */
    private function updateClickCount($event, $streamType)
    {
        $columnName = "click_count_".$streamType;
        Yii::$app->db->createCommand(
            "update subscription.log set ".$columnName." = :count where id = :event_id",
            [
                'event_id' => $event['id'],
                'count' => $event[$columnName] + 1,
            ]
        )->execute();
    }

    /**
     * @param $event
     * @param $streamType
     * @throws \yii\db\Exception
     */
    private function updateIsUnsubscribed($event, $streamType)
    {
        $columnName = "is_unsubscribed_".$streamType;
        Yii::$app->db->createCommand(
            "update subscription.log set ".$columnName." = :is_unsubed where id = :event_id",
            [
                'event_id' => $event['id'],
                'is_unsubed' => true,
            ]
        )->execute();
    }

    private function notifyIfViolationAlreadyClosed($event, $data)
    {
        // Если дошло оповещение о нарушении, а нарушение уже закрыто, нужно отправить оповещение
        // Если один из статусов (status_push или status_email) в завершённом статусе, то нотификашка должна быть отправлена
        //ранее при получении этого статуса, или же при закрытии нарушения
        if (in_array($event['event_code'], self::$violationNotificationEvents)
            && !in_array($event['status_push'], self::$deliveredActions)
            && !in_array($event['status_email'], self::$deliveredActions)
            && $this->isDelivered($data->action)) {

            $violation = Violation::findOne(['id_violation' => $event['id_violation']]);
            if ($violation && $violation->isFinished()) {
                (new ViolationModel())->notifyClosedViolation($violation);
            }
        }
    }

    /**
     * @param string $streamType
     * @return bool
     */
    private function isAvailableStreamType($streamType)
    {
        return in_array($streamType, self::$availableStreamTypes);
    }

    /**
     * @param string $action
     * @return bool
     */
    private function isDelivered($action)
    {
        return in_array($action, self::$deliveredActions);
    }

    /**
     * @param $action
     * @return bool
     */
    private function isSpecialAction($action)
    {
        return in_array($action, self::$specialActions);
    }

    /**
     * @param string $json
     * @return bool
     */
    private function logSpkResponse(string $json): bool
    {
        try {
            $path = FileHelper::normalizePath(\Yii::getAlias('@runtime/logs/spk-rabbit'));
            if (FileHelper::createDirectory($path)) {
                file_put_contents($path . '/response_' . microtime(true) . '.json', $json, FILE_TEXT);
            }
            return true;
        } catch (\Exception $e) {

        }
        return false;
    }
}
