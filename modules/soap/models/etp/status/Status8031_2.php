<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8031_2 extends Status
{
    const CODE = 8031.2;

    protected $name = 'Истек срок оплаты';
    // Управляющий статус. Скрывает кнопку оплаты
    protected $note = '';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }
}
