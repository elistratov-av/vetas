<?php

namespace app\modules\soap\models\etp\status;

class Status1075 extends Status
{
    const CODE = 1075;

    protected $code;

    protected $name = 'Заявитель явился на прием';
    protected $note = '';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
