<?php

namespace app\modules\soap\models\etp\status;

class Status1068 extends Status
{
    const CODE = 1068;

    protected $code;

    protected $name = 'Запрос на перенос подан';
    protected $note = 'Производится перенос записи на прием в ветеринарное учреждение по инициативе заявителя.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
