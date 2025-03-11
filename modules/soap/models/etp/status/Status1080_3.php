<?php

namespace app\modules\soap\models\etp\status;

class Status1080_3 extends Status1080 implements StatusReasonInterface
{
    const CODE = 1080.3;

    protected $reason = 3;

    protected $name = 'Запись отменена в связи с неоплатой счета';
    protected $note = 'Запись на прием к ветеринарному врачу отменена в связи с неоплатой счета за услугу.';

    public function __construct()
    {
        $this->code = Status1080::CODE;
    }

    public function getReasonCode() : int
    {
        return $this->reason;
    }
}
