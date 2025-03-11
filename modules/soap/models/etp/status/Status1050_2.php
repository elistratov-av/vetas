<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status1050_2 extends Status
{
    const CODE = 1050.2;

    protected $name = 'Запись произведена';
    protected $note = 'Произведена запись на онлайн-консультацию. '
        .'Дата и время приема: %s. '
        .'Ссылка на оплату: %s. '
        .'Ссылка на онлайн-консультацию будет направлена в личный кабинет '
        .'в раздел «Заявки», а также продублирована на электронную почту после оплаты. '
        .'Услугу необходимо оплатить в течение 15 минут.'
    ;
    protected $payment_url;

    public function __construct()
    {
        $this->code = self::CODE;
    }

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    //TODO: добавить ссылку на оплату
    public function getNote($message) : string
    {
        $visitTime = substr($message->getVisit()->time_range, 13, 5);
        $visitDate = substr($message->getVisit()->time_range, 2, 10);

        return sprintf($this->note, $visitDate . ' ' . $visitTime, $this->payment_url);
    }

    public function setPaymentUrl(string $paymentUrl): void
    {
        $this->payment_url = $paymentUrl;
    }
}
