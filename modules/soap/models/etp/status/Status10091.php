<?php

namespace app\modules\soap\models\etp\status;

class Status10091 extends Status
{
    const CODE = 10091;

    protected $code;

    protected $name = 'Изменение заявления возможно';
    protected $note = 'Начало возможности переноса записи на прием к ветеринарному врачу.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
