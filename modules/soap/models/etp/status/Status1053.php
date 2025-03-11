<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1053 extends Status
{
    const CODE = 1053;

    protected $code;

    protected $name = 'Запись перенесена по инициативе заявителя';
    protected $note = 'Запись на прием к ветеринарному врачу (номер талона: %s) перенесена по инициативе заявителя. Новый номер талона: %s';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     * @throws \yii\db\Exception
     */
    public function getNote($message) : string
    {
        return sprintf($this->note, $message->getVisit()->getOldTicketNumber(), $message->getVisit()->ticket_number);
    }
}
