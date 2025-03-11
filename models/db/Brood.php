<?php

namespace app\models\db;

/**
 * Class Brood
 * @package app\models\db
 *
 * @property int    $id
 * @property int    $pet_count
 * @property int    $id_owner
 * @property int    $id_owner_type
 * @property int    $id_breed
 * @property int    $id_species
 * @property string $birthday
 * @property bool   $is_active
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \app\models\db\PetOwners $owner
 * @property \app\models\db\Pets      $pets
 * @property \app\models\db\Species   $species
 * @property \app\models\db\Breeds    $breeds
 */
class Brood extends ActiveRecord
{
    const DOG_TIME_MONTHS = 6;
    const CAT_TIME_MONTHS = 6;

    const MIN_PETS_COUNT = 2;
    const MAX_PETS_COUNT = 15;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'public.broods';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['pet_count', 'id_owner', 'id_owner_type', 'id_species', 'birthday'], 'required'],
            [['id', 'pet_count', 'id_owner', 'id_owner_type', 'id_species', 'id_breed'], 'integer'],
            [['is_active'], 'boolean'],
            [['id_owner'], 'exist', 'skipOnError' => false, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['id_breed'], 'exist', 'skipOnError' => true, 'targetClass' => Breeds::class, 'targetAttribute' => ['id_breed' => 'id']],
            [['id_species'], 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => ['id_species' => 'id']],
            [['birthday'], 'safe'], // TODO - смотря в каком формате будет приходить с фронта
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'safe'],
        ];
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
    public function getSpecies()
    {
        return $this->hasOne(Species::class, ['id' => 'id_species']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreed()
    {
        return $this->hasOne(Breeds::class, ['id' => 'id_breed']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id_brood' => 'id']);
    }
}
