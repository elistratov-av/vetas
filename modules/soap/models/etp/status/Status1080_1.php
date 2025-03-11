<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1080_1 extends Status1080 implements StatusReasonInterface
{
    const CODE = 1080.1;

    protected $reason = 1;

    protected $name = 'Запись отменена по инициативе ветеринарной клиники';
    protected $note = 'Запись на прием к ветеринарному врачу отменена (номер талона: %s) по инициативе ветеринарного учреждения «%s». Номер телефона: %s';

    public function __construct()
    {
        $this->code = Status1080::CODE;
    }

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    public function getNote($message) : string
    {
        $visit = $message->getVisit();
        $organization = $visit->organization;
        return sprintf($this->note, $visit->ticket_number, $organization->name, $organization->getTelephone());
    }

    public function getReasonCode() : int
    {
        return $this->reason;
    }
}
