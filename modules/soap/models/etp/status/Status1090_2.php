<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1090_2 extends Status
{
    const CODE = 1090.2;

    protected $name = 'Запись отменена по инициативе заявителя';
    protected $note = 'Запись на онлайн-консультацию к ветеринарному врачу отменена (дата и время: %s)'
                        .'Деньги поступят в среднем в течение 10 рабочих дней. Если деньги не поступили через 10 рабочих дней, '
                        .'необходимо написать на электронную почту mosobvet@vet.mos.ru для получения платежного поручения. '
                        .'В письме укажите ФИО. Далее обратитесь в свой банк.';

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
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $message->getVisit()->updated_at);
        return sprintf($this->note, $date->format('Y-m-d H:i'));
    }
}
