<?php

namespace app\common\components\inform\events;

use app\models\db\Order;
use app\models\db\PetOwners;
use app\models\db\Users;
use app\models\db\Violation;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;

class PrimaryViolationOrderEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'primary_violation_order';

    /** @var Violation */
    public $violation;

    /** @var Order */
    public $order;

    /** @var Users */
    public $author;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->violation->owner->sso_id;
    }

    public function getEventData($token): array
    {
        if (!$this->violation->feedback_token)
            (new ViolationModel())->generateNewFeedbackToken($this->violation);

        /** @var PetOwners $owner */
        $owner = $this->violation->owner;

        $eventData = [
            'number' => $this->order->number,
            'order_date' => $this->order->date_order,
            'current_date' => (new \Datetime())->format('Y-m-d'),
            'until_date' => $this->order->date_to,
            'io' => $owner->i_fio .' '. $owner->o_fio,
            'fio' => $owner->f_fio .' '. $owner->i_fio .' '. $owner->o_fio,
            'pet_name' => $this->violation->pet->name,
            'date' => $this->violation->date_violation,
            'ref' => sprintf($this->getService()->linkToVaccineFeedbackForm, $this->violation->feedback_token),
            'link' => $this->getUnsubscribeLink($token),
            'user' => $this->author->f_fio .' '. $this->author->i_fio .' '. $this->author->o_fio,
        ];
        if ($this->files_token) $eventData['files_link'] = sprintf($this->getService()->linkToFilePage, $this->files_token);

        return $eventData;
    }
}
