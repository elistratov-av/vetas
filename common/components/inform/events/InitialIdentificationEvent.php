<?php

namespace app\common\components\inform\events;

use app\models\db\PetOwners;
use app\models\db\Pets;

/**
 * Событие: Уведомление о необходимости чипирования
 *
 * Class InitialIdentificationEvent
 * @package app\common\components\inform\events
 */
class InitialIdentificationEvent extends ElkEvent
{
    const EVENT_CODE = 'initial_info_identification';

    /**
     * @var PetOwners
     */
    public $owner;
    /**
     * @var Pets
     */
    public $pet;

    /**
     * @inheritDoc
     */
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
        $data = [
            'io' => $this->owner->getNameForInformation(),
            'link' => $this->getUnsubscribeLink($token)
        ];

        $pet_name = trim($this->pet->name);
        if (!empty($pet_name)) {
            $data['pet_name'] = $pet_name;
        }

        return $data;
    }
}
