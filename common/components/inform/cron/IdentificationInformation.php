<?php

namespace app\common\components\inform\cron;

use app\common\components\inform\events\RemindIdentificationEvent;
use app\common\components\inform\events\SubscriptionEvent;
use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\models\db\Pets;
use yii\db\Expression;

/**
 * Class IdentificationInformation
 * @package app\common\components\inform\cron
 */
class IdentificationInformation extends Information
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return RemindIdentificationEvent::EVENT_CODE;
    }

    /**
     * @param Contacts[] $contacts
     * @param Pets $pet
     * @param PetOwners $owner
     * @return RemindIdentificationEvent|SubscriptionEvent
     */
    public function getEvent($contacts, Pets $pet, PetOwners $owner)
    {
        return new RemindIdentificationEvent([
            'owner' => $owner,
            'pet' => $pet,
            'contacts' => $contacts,
            'id_pet' => $pet->id
        ]);
    }

    /**
     * @return \app\models\db\Pets[]
     */
    public function getPetsForInformation()
    {
        /** @var Pets[] $result */
        $result = Pets::find()
            ->distinct()
            ->forInformation()
            ->withoutIdentification()
            ->withInformationLog($this->getType())
            ->andWhere(new Expression(
                "(information_log.pet_id IS NULL OR information_log.date < (now() - make_interval(months => :months))::date)"),
                [
                    'months' => 6
                ]
            )
            ->each($this->batchSize);

        return $result;
    }
}
