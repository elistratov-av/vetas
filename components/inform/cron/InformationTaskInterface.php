<?php

namespace app\common\components\inform\cron;

use app\common\components\inform\events\SubscriptionEvent;
use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\models\db\Pets;

interface InformationTaskInterface
{
    public function handle();

    /**
     * @return Pets[]||array
     */
    public function getPetsForInformation();

    /**
     * @param Contacts[] $contacts
     * @param Pets $pet
     * @param PetOwners $owner
     * @return SubscriptionEvent
     */
    public function getEvent($contacts, Pets $pet, PetOwners $owner);

    /**
     * @return string
     */
    public function getType() : string;
}
