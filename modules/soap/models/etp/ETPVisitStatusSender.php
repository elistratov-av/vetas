<?php

namespace app\modules\soap\models\etp;

use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\Visits;

class ETPVisitStatusSender
{
    /** @var ETP  */
    protected $etp;

    /** @var ETPMessage|array|null|\yii\db\ActiveRecord  */
    protected $etpMessage;

    /** @var CoordinateMessage  */
    protected $message;

    /** @var Visits */
    protected $visit;

    /**
     * ETPVisitStatusSender constructor.
     * @param int $visit_id
     * @throws \Exception
     */
    public function __construct(int $visit_id)
    {
        if (!$this->visit = Visits::findOne(['id' => $visit_id])) {
            throw new \Exception("Не найден прием #{$visit_id} (v1)");
        }

        if (!$this->etpMessage = ETPMessage::findOne(['visit_id' => $visit_id])) {
            throw new \Exception("Не найдена заявка из ЕТП привязанная к приему #{$visit_id} (v1)");
        }

        /** @var CoordinateMessage $message */
        $this->message = $this->etpMessage->getCoordinateMessageInstance();
        $this->etp = \Yii::$app->getModule('soap')->etp;
    }

    /**
     * @param StatusInterface $status
     * @param null|string $status_id
     */
    public function sendStatus(StatusInterface $status, ?string $status_id)
    {
        $this->message->status_id = $status_id;
        $this->etp->sendStatusMessage($status, $this->message);
    }

    /**
     * @return string
     */
    public function getServiceNumber() : string
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
}
