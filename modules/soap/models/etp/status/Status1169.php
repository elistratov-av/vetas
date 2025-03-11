<?php

namespace app\modules\soap\models\etp\status;

class Status1169 extends Status
{
    const CODE = 1169;

    protected $code;

    protected $name = 'Отказано в отзыве заявления';
    protected $note = 'Не удалось отменить запись. Попробуйте отменить запись чуть позже.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
