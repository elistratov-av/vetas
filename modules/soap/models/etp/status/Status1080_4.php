<?php

namespace app\modules\soap\models\etp\status;

class Status1080_4 extends Status1080 implements StatusReasonInterface
{
    const CODE = 1080.4;

    protected $reason = 4;

    protected $name = 'Запись отменена по инициативе ветеринарной клиники';
    protected $note = 'Запись на онлайн-консультацию к ветеринарному врачу отменена (дата и время) по инициативе ветеринарной клиники. '
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
