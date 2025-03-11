<?php

namespace app\modules\soap\v2\queue;

use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status103099;
use app\modules\soap\models\etp\status\Status1050;
use app\modules\soap\models\etp\status\Status1050_2;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status106999;
use app\modules\soap\models\etp\status\Status1075;
use app\modules\soap\models\etp\status\Status1080_1;
use app\modules\soap\models\etp\status\Status1080_2;
use app\modules\soap\models\etp\status\Status1080_3;
use app\modules\soap\models\etp\status\Status1080_4;
use app\modules\soap\models\etp\status\Status1080_5;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\soap\models\etp\status\Status1090_2;
use app\modules\soap\models\etp\status\Status1152;
use app\modules\soap\models\etp\status\Status1168;
use app\modules\soap\models\etp\status\Status1169;
use app\modules\soap\models\etp\status\Status8021;
use app\modules\soap\models\etp\status\Status8021_2;
use app\modules\soap\models\etp\status\Status8031_1;
use app\modules\soap\models\etp\status\Status8031_2;
use app\modules\soap\models\etp\status\Status8031_4;

/**
 * Class MosruStatusSender
 * @package app\modules\soap\v2\queue
 */
class MosruStatusSender
{
    /**
     * @return \yii\queue\db\Queue
     */
    protected function getQueue()
    {
        /** @var \yii\queue\db\Queue $queue */
        $queue = \Yii::$app->get('soap_queue_v2');//

        return $queue;
    }

    /**
     * Отправка уведомления о создания приема
     * Отправка статусов 1050 10090 10091 в ЕТП
     *
     * @param integer $visit_id
     */
    public function visitCreate($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1050::CODE, Status10090::CODE, Status10091::CODE],
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
        // $this->getQueue()->push(json_encode([
        //     'visit_id' => $visit_id,
        //     'etp_status' => [Status8021::CODE],
        // ]));

        self::getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status8021::CODE],
        ]));
    }

    /**
     * Отправка уведомления о переносе приема со стороны заявителя
     * Отправка статуса 1053
     *
     * @param integer     $visit_id
     * @param string|null $status_id
     */
    public function visitChangeInMosru($visit_id, ?string $status_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1053::CODE],
            'status_id' => $status_id,
        ]));
    }

    /**
     * Отправка уведомления об ошибке при переносе приема со стороны заявителя
     * Отправка статуса 1168
     *
     * @param integer     $visit_id
     * @param string|null $status_id
     * @param string|null $errorMessage
     * @param string|null $note
     */
    public function visitChangeInMosruError($visit_id, ?string $status_id, $errorMessage = null, $note = null)
    {
        $data = [
            'visit_id' => $visit_id,
            'etp_status' => [Status1168::CODE],
            'status_id' => $status_id,
            'error_message' => $errorMessage,
        ];

        if (!empty($note)) {
            $data['note'] = $note;
        }

        $this->getQueue()->push(json_encode($data));
    }

    /**
     * Отправки уведомления о взатии приема в работу
     * Отправка статусов 10190 10191 1075
     * Альтернативные статусы для телевета 10190 1152
     *
     * @param integer $visit_id
     * @param bool    $isOnline
     */
    public function visitStart(int $visit_id, bool $isOnline = false)
    {
        // $this->getQueue()->push(json_encode([
        //     'visit_id' => $visit_id,
        //     'etp_status' => [Status10190::CODE, Status10191::CODE, Status1075::CODE],
        // ]));

        if ($isOnline) {
            $etp_status = [Status10190::CODE, Status1152::CODE];
        } else {
            $etp_status = [Status10190::CODE, Status10191::CODE, Status1075::CODE];
        }

        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => $etp_status,
        ]));
    }

    /**
     * Отправка уведомления об окончании приема
     * Отправка статуса 1075 (для телевета)
     *
     * @param integer $visit_id
     * @param bool    $isOnline
     * @return void
     */
    public function visitFinish(int $visit_id, bool $isOnline = false)
    {
        // Для телевета отправим статус 1075 по окончанию приема
        if (!$isOnline) {
            return;
        }

        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1075::CODE],
        ]));
    }

    /**
     * Отправка уведомления об отмене приема в клинике
     * Отправка статусов 10190 10191 1080.1
     * Альтернативные статусы для телевета 10190 1080.4
     *
     * @param integer $visit_id
     * @param boolean $isOnline
     */
    public function visitCancelInClinic($visit_id, bool $isOnline = false)
    {
        if ($isOnline) {
            $etp_status = [Status10190::CODE, Status1080_4::CODE, Status8021_2::CODE];
        } else {
            $etp_status = [Status10190::CODE, Status10191::CODE, Status1080_1::CODE];
        }

        // $this->getQueue()->push(json_encode([
        //     'visit_id' => $visit_id,
        //     'etp_status' => [Status10190::CODE, Status10191::CODE, Status1080_1::CODE],
        // ]));

        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => $etp_status,
        ]));
    }

    /**
     * Отправка уведомеления об отмене приема по инициативе заявителя
     * Отправка статусов 10190 10191 10190
     * Альтернативные статусы для телевета 10190 1090.2
     *
     * @param integer     $visit_id
     * @param string|null $status_id
     */
    public function visitCancelInMosru($visit_id, ?string $status_id, bool $isOnline)
    {
        if ($isOnline) {
            $etp_status = [Status10190::CODE, Status1090_2::CODE];
        } else {
            $etp_status = [Status10190::CODE, Status10191::CODE, Status1090::CODE];
        }

        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => $etp_status,
            'status_id' => $status_id,
        ]));
    }

    /**
     * Отправка уведомления об ошибке при отмене приема со стороны заявителя
     * Отправка статуса 1169
     * Альтернативный статус для телевета 106999
     *
     * @param integer     $visit_id
     * @param string|null $status_id
     * @param string|null $errorMessage
     */
    public function visitCancelInMosruError($visit_id, ?string $status_id, $errorMessage = null, $isOnline = false)
    {
        $statusCode = $isOnline ? Status106999::CODE : Status1169::CODE;
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [$statusCode],
            'status_id' => $status_id,
            'error_message' => $errorMessage,
        ]));
    }

    /**
     * Отправка уведомления о прошествии 2х недель с момента врермени начала приема
     * Отправка статус 1080.2
     *
     * @param integer $visit_id
     */
    public function visitDelay($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1080_2::CODE],
        ]));
    }

    /**
     * Отправка уведомления о наступлении времени начала приема
     * Отправка статусов 10190 10191
     *
     * @param integer $visit_id
     */
    public function visitTimeStart($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status10190::CODE, Status10191::CODE],
        ]));
    }

    /**
     * Отправка уведомления об ошибке при записи на прием
     * Отправка статуса 103099
     *
     * @param string      $service_number
     * @param string|null $errorMessage
     */
    public function visitCreateError($service_number, $errorMessage = null)
    {
        $this->getQueue()->push(json_encode([
            'service_number' => $service_number,
            'etp_status' => [Status103099::CODE],
            'error_message' => $errorMessage,
        ]));
    }

    /**
     * Отправка уведомления о необходимости оплаты
     * Отправка статуса 1050.2
     *
     * @param integer $visit_id
     * @param string  $paymentUrl
     */
    public function visitRequirePayment($visit_id, $paymentUrl)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1050_2::CODE],
            'payment_url' => $paymentUrl,
        ]));
    }

    /**
     * Отправка уведомления об успешной оплате приёма
     * Отправка статуса 8031.1
     *
     * @param integer $visit_id
     */
    public function visitPaid($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status8031_1::CODE],
        ]));
    }

    /**
     * Отправка уведомления об истечении срока оплаты и отмена записи приёма в связи с неоплатой счёта
     * Отправка статуса 8031.2 1080.3
     *
     * @param integer $visit_id
     */
    public function visitCancelPaymentByExpirement($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status8031_2::CODE, Status1080_3::CODE],
        ]));
    }

    /**
     * Отправка уведомления о получении реквизитов для возврата денежных средств
     * Отправка статуса 8031.4
     *
     * @param $visit_id
     * @return void
     */
    public function visitPaymentDataReceived($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status8031_4::CODE]
        ]));
    }

    /**
     * Отправка уведомления об отмене приёма по техническим причинам
     * Отправка статуса 1080.5 8021.2
     *
     * @param $visit_id
     * @return void
     */
    public function visitCancelByTechReason($visit_id)
    {
        $this->getQueue()->push(json_encode([
            'visit_id' => $visit_id,
            'etp_status' => [Status1080_5::CODE, Status8021_2::CODE],
        ]));
    }
}
