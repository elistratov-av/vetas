<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8031_3 extends Status
{
    const CODE = 8031.3;

    protected $name = 'Ввод реквизитов недоступен';
    // Управляющий статус. Пользователю недоступна кнопка [Предоставить реквизиты]
    protected $note = '';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }
}
