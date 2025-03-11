<?php

namespace app\models\db;

/**
 * This is the model class for table "pets_to_owner".
 *
 * @property int $id
 * @property int $id_owner
 * @property int $id_pet
 * @property int $id_owner_type
 *
 * @property PetOwnerType $owner_type
 * @property PetOwners $owner
 * @property Pets $pet
 */
class PetsToOwner extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_owner', 'id_pet', 'id_owner_type'], 'required'],
            [['id_owner', 'id_pet', 'id_owner_type'], 'default', 'value' => null],
            [['id_owner', 'id_pet', 'id_owner_type'], 'integer'],
            [
                ['id_pet', 'id_owner'], 'unique', 'targetAttribute' => ['id_pet', 'id_owner'],
                'message' => 'Животное уже связано с этим владельцем',
            ],
            [['id_owner_type'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwnerType::class, 'targetAttribute' => ['id_owner_type' => 'id']],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_owner' => 'Id Owner',
            'id_pet' => 'Id Pet',
            'id_owner_type' => 'Id Owner Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner_type()
    {
        return $this->hasOne(PetOwnerType::class, ['id' => 'id_owner_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }
}
