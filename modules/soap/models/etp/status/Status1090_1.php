<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1090_1 extends Status
{
    const CODE = 1090.1;

    protected $name = 'Запись отменена по инициативе заявителя';
    protected $note = 'Прием в клинике / на дому: Запись на прием к ветеринарному врачу отменена (номер талона: %s).';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = Status1090::CODE;
    }

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    public function getNote($message) : string
    {
        return sprintf($this->note, $message->getVisit()->ticket_number);
    }
}
