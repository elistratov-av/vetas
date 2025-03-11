<?php

namespace app\modules\soap\models\etp\status;

class Status1152 extends Status
{
    const CODE = 1152;

    protected $code;

    protected $name = 'Начало онлайн - консультации';
    // Управляющий статус
    protected $note = '';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
