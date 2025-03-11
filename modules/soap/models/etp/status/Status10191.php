<?php

namespace app\modules\soap\models\etp\status;

class Status10191 extends Status
{
    const CODE = 10191;

    protected $code;

    protected $name = 'Изменение заявления невозможно';
    protected $note = 'Конец возможности переноса записи на прием к ветеринарному врачу.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
