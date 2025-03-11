<?php

namespace app\modules\soap\models\etp\status;

class Status10090 extends Status
{
    const CODE = 10090;

    protected $code;

    protected $name = 'Отзыв заявления возможен';
    protected $note = 'Начало возможности отмены записи на прием к ветеринарному врачу.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
