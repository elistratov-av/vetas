<?php

namespace app\modules\soap\v2\models\db;

use app\modules\soap\models\PetOwners;
use app\modules\soap\models\Pets;

/**
 * Class ETPMessage
 * @package app\modules\soap\v2\models
 *
 * @property int $id
 * @property int $visit_id
 * @property string $service_number
 * @property string $message
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $sso_id
 * @property string $phone
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $system_id
 * @property string|null $message_id
 */
class ETPMessage extends \app\models\db\etp\ETPMessage
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'etp.message_v2';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnimal()
    {
        return $this
            ->hasOne(Pets::class, ['id' => 'id_pet'])
            ->viaTable('visits', ['id' => 'visit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this
            ->hasOne(PetOwners::class, ['id' => 'id_owner'])
            ->viaTable('visits', ['id' => 'visit_id']);
    }
}
