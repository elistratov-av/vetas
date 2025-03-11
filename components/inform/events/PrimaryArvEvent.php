<?php

namespace app\common\components\inform\events;

use app\models\db\Order;
use app\models\db\Users;
use app\models\db\Violation;
use app\models\db\ViolationToARV;

class PrimaryArvEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'primary_AVR';

    /** @var Violation */
    public $violation;

    /** @var ViolationToARV */
    public $arv;

    /** @var Users */
    public $author;

    /**
     * Блок информации по нарушению для владельца, заполняемый инспектором при работе с нарушением
     * @var string
     */
    public $text;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->violation->owner->sso_id;
    }

    public function getEventData($token): array
    {
        /** @var Order $order */
        $order = Order::find()->where(['number' => $this->arv->number])->one();
        $owner = $this->violation->owner;

        $eventData = [
//            'decree_number' => $this->arv->decree_number,
            'user' => $this->author->f_fio .' '. $this->author->i_fio .' '. $this->author->o_fio,
            'fio' => $owner->f_fio .' '. $owner->i_fio .' '. $owner->o_fio,
            'pet_name' => $this->violation->pet->name,
            'date' => $this->violation->date_violation,
            'primary_order_number' => $order->number,
            'primary_order_date' => $order->date_order,
//            'sum' => $this->arv->sum,
            'text' => $this->text,
            'link' => $this->getUnsubscribeLink($token),
        ];
        if ($this->files_token) $eventData['files_link'] = sprintf($this->getService()->linkToFilePage, $this->files_token);

        return $eventData;
    }
}
