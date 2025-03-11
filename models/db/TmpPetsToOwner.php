<?php

namespace app\models\db;

use Yii;

/**
 * pets_tmp - n:m - pet_owners_tmp
 *
 * @property int $id
 * @property int $id_owner_tmp
 * @property int $id_pet_tmp
 * @property int $id_owner_type
 *
 * @property TmpPetOwners $tmpWwner
 * @property PetOwnerType $ownerType
 * @property TmpPets $tmpPet
 */
class TmpPetsToOwner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_owner_tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Модель создаётся только при создании модели PetsToOwner, валидирующей эти же данные
            [['id_owner_tmp', 'id_pet_tmp', 'id_owner_type'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmpOwner()
    {
        return $this->hasOne(TmpPetOwners::class, ['id' => 'id_owner_tmp']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwnerType()
    {
        return $this->hasOne(PetOwnerType::class, ['id' => 'id_owner_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmpPet()
    {
        return $this->hasOne(TmpPets::class, ['id' => 'id_pet_tmp']);
    }
}
