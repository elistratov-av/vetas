<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1050 extends Status
{
    const CODE = 1050;

    protected $code;

    protected $name = 'Запись произведена';
    protected $note = 'Произведена запись к ветеринарному врачу в ветеринарное учреждение. Номер талона: %s';

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
     */
    public function getNote($message) : string
    {
        return sprintf($this->note, $message->getVisit()->ticket_number);
    }
}
