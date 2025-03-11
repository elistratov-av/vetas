<?php

namespace app\modules\soap\models\etp\status;

class Status106999 extends Status
{
    const CODE = 106999;

    protected $code;

    protected $name = 'Технический сбой';
    protected $note = 'Не удалось отменить запись. Попробуйте повторить отмену записи чуть позже.';

    public function __construct()
    {
        $this->code = self::CODE;
    }
}
