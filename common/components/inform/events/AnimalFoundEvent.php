<?php

namespace app\common\components\inform\events;

use app\models\db\Pets;

/**
 * Событие: Уведомление о найденном/отловленном владельческом животном
 *
 * Class AnimalFoundEvent
 * @package app\common\components\inform\events
 */
class AnimalFoundEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'animal_found';

    /** @var Pets */
    public $pet;

    /**
     * Номер чипа животного
     * @var string
     */
    public $petMicrochip;

    /**
     * Адрес и наименование места содержания животного
     * @var  string
     */
    public $address;

    /**
     * Телефон места содержания (в событии должен быть по маске +7(ххх)ххх-хх-хх)
     * @var string
     */
    public $phone;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->pet->owner->sso_id;
    }

    /**
     * @return array
     */
    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        return [
            'io' => $this->pet->owner->getNameForInformation(),
            'pet_name' => $this->pet->name,
            'pet_microchip' => $this->petMicrochip,
            'address' => $this->address,
            'phone' => $this->phone,
            'link' => $this->getUnsubscribeLink($token)
        ];
    }

}
