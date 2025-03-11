<?php

namespace app\common\components\inform\events;

use app\models\db\Violation;
use app\models\db\ViolationType;

class CancelViolationEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'cancel_violation';

    /** @var Violation */
    public $violation;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->violation->owner->sso_id;
    }

    public function getEventData($token): array
    {
        switch ($this->violation->type->type) {
            case ViolationType::TYPE_VACCINATION_VIOLATION:
                $violationType = 'вакцинации';
                break;
            case ViolationType::TYPE_IDENT_VIOLATION:
                $violationType = 'идентификации';
                break;
            default:
                $violationType = '';
                break;
        }
        $owner = $this->violation->owner;
        return [
            'io' => $owner->i_fio .' '. $owner->o_fio,
            'violation_type' => $violationType,
            'pet_name' => $this->violation->pet->name,
            'date' => $this->violation->date_violation,
            'link' => $this->getUnsubscribeLink($token)
        ];
    }
}
