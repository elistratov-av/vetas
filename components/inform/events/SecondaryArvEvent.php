<?php

namespace app\common\components\inform\events;

use app\models\db\Order;
use app\models\db\Users;
use app\models\db\Violation;
use app\models\db\ViolationToARV;

class SecondaryArvEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'secondary_AVR';

    /** @var Violation */
    public $violation;

    /** @var Users */
    public $author;

    /** @var ViolationToARV */
    public $arv;

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
        /** @var Order[] $orders */
        $orders = Order::find()->where(['id_violation' => $this->arv->id_violation])->all();
        $array_order = [];
        foreach ($orders as $order) {
            array_push($array_order, $order->number);
            array_push($array_order, $order->date_order);
        }
        $owner = $this->violation->owner;
        $eventData = [
//            'decree_number' => $this->arv->decree_number,
            'user' => $this->author->f_fio .' '. $this->author->i_fio .' '. $this->author->o_fio,
            'fio' => $owner->f_fio .' '. $owner->i_fio .' '. $owner->o_fio,
            'pet_name' => $this->violation->pet->name,
            'date' => $this->violation->date_violation,
            'array_order' => $array_order,
//            'sum' => $this->arv->sum,
            'text' => $this->text,
            'link' => $this->getUnsubscribeLink($token),
        ];
        if ($this->files_token) $eventData['files_link'] = sprintf($this->getService()->linkToFilePage, $this->files_token);

        return $eventData;
    }
}
