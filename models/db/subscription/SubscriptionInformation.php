<?php

namespace app\models\db\subscription;

use app\models\db\ActiveRecord;
use app\models\db\PetOwners;
use app\models\db\Pets;

/**
 * This is the model class for table "subscription.information".
 *
 * @property int $id
 * @property string $type Тип уведомления
 * @property int $pet_id
 * @property int $owner_id
 * @property string $date
 */
class SubscriptionInformation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription.information';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'pet_id', 'owner_id'], 'required'],
            [['pet_id', 'owner_id'], 'default', 'value' => null],
            [['pet_id', 'owner_id'], 'integer'],
            [['date'], 'safe'],
            [['type'], 'string', 'max' => 32],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['owner_id' => 'id']],
            [['pet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['pet_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'pet_id' => 'Pet ID',
            'owner_id' => 'Owner ID',
            'date' => 'Date',
        ];
    }
}
