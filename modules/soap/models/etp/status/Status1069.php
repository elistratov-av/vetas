<?php

namespace app\modules\soap\models\etp\status;

class Status1069 extends Status
{
    const CODE = 1069;

    protected $code;

    protected $name = 'Получен запрос на отзыв заявления';
    protected $note = 'Производится отмена записи на прием в ветеринарное учреждение по инициативе заявителя.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
