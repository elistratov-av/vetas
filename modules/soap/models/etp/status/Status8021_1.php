<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8021_1 extends Status
{
    const CODE = 8021.1;

    protected $name = 'Необходимо произвести оплату';
    // Управляющий статус. открывает кнопку оплаты на финальном шаге экранной формы и в ЛК в статусе 1050
    protected $note = '';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
