<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1050_1 extends Status
{
    const CODE = 1050.1;

    protected $name = 'Запись произведена';
    protected $note = 'Прием в клинике / на дому: '
        .'Произведена запись к ветеринарному врачу. '
        .'Номер талона: %s.'
        .'Посмотреть, отменить или перенести запись %s.'
    ;

    public function __construct()
    {
        $this->code = self::CODE;
    }

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    //TODO: добавить ссылку на форму
    public function getNote($message) : string
    {
        return sprintf($this->note, $message->getVisit()->ticket_number);
    }
}
