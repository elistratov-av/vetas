<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8031_4 extends Status
{
    const CODE = 8031.4;

    protected $name = 'Реквизиты получены';
    protected $note = 'Реквизиты для возврата денежных средств получены.'
                        .'Деньги поступят в среднем в течение 10 рабочих дней. Если деньги не поступили через 10 рабочих дней, '
                        .'необходимо написать на электронную почту mosobvet@vet.mos.ru для получения платежного поручения. '
                        .'В письме укажите ФИО. Далее обратитесь в свой банк.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }
}
