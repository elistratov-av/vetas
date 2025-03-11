<?php

namespace app\common\components\inform\events;

use app\models\db\OwnerFeedback;
use app\models\db\Violation;

class FeedbackDeclinedEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'info_declined';

    /** @var Violation */
    public $violation;

    /** @var OwnerFeedback */
    public $owner_feedback;

    /**
     * Блок информации для владельца, заполняемый инспектором при отклонении данных предоставленых владельцем
     * @var string
     */
    public $text;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->violation->owner->sso_id;
    }

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        $owner = $this->violation->owner;

        return [
            'text' => $this->text,
            'link' => $this->getUnsubscribeLink($token),
            'pet_name' => $this->violation->pet->name,
            'io' => $owner->i_fio .' '. $owner->o_fio,
            'date' => $this->owner_feedback->created_at,
        ];
    }
}
