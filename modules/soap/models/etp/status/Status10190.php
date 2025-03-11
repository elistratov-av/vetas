<?php

namespace app\modules\soap\models\etp\status;

class Status10190 extends Status
{
    const CODE = 10190;

    protected $code;

    protected $name = 'Отзыв заявления невозможен';
    protected $note = 'Конец возможности отмены записи на прием к ветеринарному врачу.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
