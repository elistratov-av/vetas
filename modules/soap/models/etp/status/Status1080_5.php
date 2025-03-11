<?php

namespace app\modules\soap\models\etp\status;

class Status1080_5 extends Status1080 implements StatusReasonInterface
{
    const CODE = 1080.5;

    protected $reason = 5;

    protected $name = 'Онлайн консультация не предоставлена';
    protected $note = 'Онлайн-консультация к ветеринарному врачу (дата и время) не может быть предоставлена по техническим причинам. '
                        .'Для возврата денежных средств в течении 30 дней заполните реквизиты.';

    public function __construct()
    {
        $this->code = self::CODE;
    }

    public function getReasonCode() : int
    {
        return $this->reason;
    }
}
