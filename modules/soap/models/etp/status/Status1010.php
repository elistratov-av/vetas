<?php

namespace app\modules\soap\models\etp\status;

class Status1010 extends Status
{
    const CODE = 1010;

    protected $code;

    protected $name = 'Заявление подано';
    protected $note = 'Производится запись в ветеринарное учреждение.';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
