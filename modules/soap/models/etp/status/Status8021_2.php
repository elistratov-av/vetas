<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8021_2 extends Status
{
    const CODE = 8021.2;

    protected $name = 'Необходимо ввести реквизиты';
    // Управляющий статус. Пользователю доступна кнопка [Предоставить реквизиты]. Формируется в статусе 1080.4, 1080.5
    protected $note = '';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
