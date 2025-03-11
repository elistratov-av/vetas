<?php

namespace app\common\components\inform\cron;

use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\SubscriptionService;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\subscription\SubscriptionInformation;
use yii\db\Expression;

/**
 * Class Information
 * @package app\common\components\inform\cron
 */
abstract class Information implements InformationTaskInterface
{
    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(int $batchSize = 1000)
    {
        $this->batchSize = $batchSize;
    }

    /**
     * @throws \app\common\components\inform\InformException
     */
    public function handle()
    {
        /** @var Pets  $pets */
        $pets = $this->getPetsForInformation();

        foreach ($pets as $pet) {
            $owners = $this->getOwnersForInformation($pet);

            foreach ($owners as $owner) {
                if (!$owner->hasSubscriptions()) {
                    continue;
                }

                $contacts = SubscriptionService::getOwnerSubscriptions($owner);
                \Yii::$app->trigger(
                    SubscriptionEventInterface::EVENT_NAME,
                    $this->getEvent($contacts, $pet, $owner)
                );
                $this->log($this->getType(), $pet, $owner);
            }
        }
    }

    /**
     * @param Pets $pet
     * @return PetOwners[]|array
     */
    protected function getOwnersForInformation(Pets $pet)
    {
        return PetOwners::find()
            ->distinct()
            ->innerJoinWith(['petsToOwners'], false)
            ->where(['pets_to_owner.id_pet' => $pet->id])
            ->andWhere(['pets_to_owner.id_owner_type' => 1])
            ->andWhere(new Expression('(pet_owners.is_main = true or pet_owners.is_main isnull)'))
            ->all();
    }

    /**
     * @param string $type
     * @param Pets $pet
     * @param PetOwners $owner
     * @throws \Exception
     */
    protected function log(string $type, Pets $pet, PetOwners $owner)
    {
        $log = new SubscriptionInformation([
            'type' => $type,
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'date' => (new \DateTime())->format(DATE_ISO8601)
        ]);
        $log->save();
    }
}
