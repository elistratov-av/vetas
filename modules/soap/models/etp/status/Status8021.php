<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8021 extends Status
{
    const CODE = 8021;

    protected $code;

    protected $name = 'Запись перенесена по инициативе клиники';
    protected $note = 'Запись на прием (номер талона: %s) изменена ветеринарным учреждением «%s». Номер телефона: %s. Новый номер талона: %s';

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
        $visit = $message->getVisit();
        $organization = $visit->organization;
        return sprintf(
            $this->note,
            $visit->getOldTicketNumber(),
            $organization->name,
            $organization->getTelephone(),
            $visit->ticket_number
        );
    }
}
