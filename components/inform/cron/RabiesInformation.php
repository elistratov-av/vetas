<?php

namespace app\common\components\inform\cron;

use app\common\components\inform\events\RemindVaccinationEvent;
use app\common\components\inform\events\SubscriptionEvent;
use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\models\db\Pets;
use yii\db\Expression;

/**
 * Class RabiesInformation
 * @package app\common\components\inform\cron
 */
class RabiesInformation extends Information
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return RemindVaccinationEvent::EVENT_CODE;
    }

    /**
     * @param Contacts[] $contacts
     * @param Pets $pet
     * @param PetOwners $owner
     * @return RemindVaccinationEvent|SubscriptionEvent
     */
    public function getEvent($contacts, Pets $pet, PetOwners $owner)
    {
        return new RemindVaccinationEvent([
            'contacts' => $contacts,
            'owner' => $owner,
            'pet' => $pet,
            'id_pet' => $pet->id
        ]);
    }

    /**
     * @return \app\models\db\Pets[]
     * @throws \Exception
     */
    public function getPetsForInformation()
    {
        /** @var Pets[] $result */
        $result = Pets::find()
            ->forInformation()
            ->withRabiesVaccination()
            ->withInformationLog($this->getType())
            ->andWhere(new Expression("(information_log.pet_id IS NULL OR information_log.date < rabies_vaccination.inform_date)"))
            ->each($this->batchSize);

        return $result;
    }
}
