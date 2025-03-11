<?php

namespace app\modules\soap\models\etp\status;

class Status1080_2 extends Status1080 implements StatusReasonInterface
{
    const CODE = 1080.2;

    protected $reason = 2;

    protected $name = 'Технический статус';
    protected $note = '';

    public function __construct()
    {
        $this->code = Status1080::CODE;
    }

    public function getReasonCode() : int
    {
        return $this->reason;
    }
}
