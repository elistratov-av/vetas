<?php

namespace app\common\components\inform\events;

use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Species;

/**
 * Событие: Уведомление о необходимости вакцинации
 *
 * Class InitialVaccinationEvent
 * @package app\common\components\inform\events
 */
class InitialVaccinationEvent extends ElkEvent
{
    const EVENT_CODE = 'initial_info_vaccination';

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
            'recommendation_text' => $this->getRecommendationText(),
            'link' => $this->getUnsubscribeLink($token)
        ];

        $pet_name = trim($this->pet->name);
        if (!empty($pet_name)) {
            $data['pet_name'] = $pet_name;
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getRecommendationText()
    {
        switch ($this->pet->species->tech_name) {
            case Species::TECH_NAME_CAT:
                return 'кошку против вирусного ринотрахеита, кальцивироза и панлейкопении';
            case Species::TECH_NAME_DOG:
                return 'собаку против лептоспироза, чумы плотоядных, инфекционного гепатита, парвовирусной и аденовирусной инфекции';
            default:
                return '';
        }
    }
}
