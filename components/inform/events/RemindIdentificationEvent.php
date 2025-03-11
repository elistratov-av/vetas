<?php

namespace app\common\components\inform\events;

use app\models\db\PetOwners;
use app\models\db\Pets;

/**
 * Событие: Напоминание об идентификации
 *
 * Class RemindIdentificationEvent
 * @package app\common\components\inform\events
 */
class RemindIdentificationEvent extends ElkEvent
{
    const EVENT_CODE = 'remind_identification';

    /** @var PetOwners */
    public $owner;

    /** @var Pets */
    public $pet;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->owner->sso_id;
    }

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        return [
            'io' => $this->owner->getNameForInformation(),
            'pet_name' => $this->pet->name,
            'link' => $this->getUnsubscribeLink($token)
        ];
    }

}
