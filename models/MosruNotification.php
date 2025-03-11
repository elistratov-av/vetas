<?php

namespace app\models;

use app\models\db\Visits;
use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status1050;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status1075;
use app\modules\soap\models\etp\status\Status1080_1;
use app\modules\soap\models\etp\status\Status1080_2;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\soap\models\etp\status\Status8021;
use yii\queue\db\Queue;

class MosruNotification
{
    /**
     * @return Queue
     */
    protected static function getQueue()
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->soap_queue;
        return $queue;
    }

    /**
     * Отправка уведомления о создания приема
     * Отправка статусов 1050 10090 10091 в ЕТП
     *
     * @param integer $visit_id
     */
    public static function visitCreate($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1050::CODE, Status10090::CODE, Status10091::CODE]
        ]));
    }
    
    /**
     * Отправка уведомления об изменении приема в клинике
     * Отправка статуса 8021
     *
     * @param integer $visit_id
     */
    public static function visitChangeInClinic($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status8021::CODE]
        ]));
    }

    /**
     * Отправка уведомления об изменении приема со стороны заявителя
     * Отправка статуса 1053
     *
     * @param integer $visit_id
     * @param string|null $status_id
     */
    public static function visitChangeInMosru($visit_id, ?string $status_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1053::CODE],
            'status_id' => $status_id
        ]));
    }

    /**
     * Отправки уведомления о взатии приема в работу
     * Отправка статусов 10190 10191 1075
     *
     * @param integer $visit_id
     */
    public static function visitStart($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status10190::CODE, Status10191::CODE, Status1075::CODE]
        ]));
    }

    /**
     * Отправка уведомления об отмене приема в клинике
     * Отправка статусов 10190 10191 1080.1
     *
     * @param integer $visit_id
     */
    public static function visitCancelInClinic($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status10190::CODE, Status10191::CODE, Status1080_1::CODE]
        ]));
    }

    /**
     * Отправка уведомеления об отмене приема по инициативе заявителя
     * Отправка статусов 10190 10191 10190
     *
     * @param integer $visit_id
     * @param string|null $status_id
     */
    public static function visitCancelInMosru($visit_id, ?string $status_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status10190::CODE, Status10191::CODE, Status1090::CODE],
            'status_id' => $status_id
        ]));
    }

    /**
     * Отправка уведомления о прошествии 2х недель с момента врермени начала приема
     * Отправка статус 1080.2
     *
     * @param integer $visit_id
     */
    public static function visitDelay($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1080_2::CODE]
        ]));
    }

    /**
     * Отправка уведомления о наступлении времени начала приема
     * Отправка статусов 10190 10191
     *
     * @param integer $visit_id
     */
    public static function visitTimeStart($visit_id)
    {
        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status10190::CODE, Status10191::CODE]
        ]));
    }
}
