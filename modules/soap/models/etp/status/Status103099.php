<?php

namespace app\modules\soap\models\etp\status;

class Status103099 extends Status
{
    const CODE = 103099;

    protected $code;

    protected $name = 'Технический сбой';
    protected $note = 'Технический сбой. Не удалось записаться. Попробуйте повторить запись чуть позже.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

}
