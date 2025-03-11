<?php

namespace app\models\db\elk;

use app\models\db\ActiveRecord;
use app\models\db\Breeds;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Species;
use app\models\db\Violation;
use app\models\db\Visits;

/**
 * This is the model class for table "elk.pets".
 *
 * @property int $id
 * @property int $id_pet
 * @property string $ext_id
 * @property int $id_elk_owner
 * @property int $id_pet_owner
 * @property int $id_species
 * @property int $id_breed
 * @property string $name
 * @property string $chip
 * @property string $birthday
 * @property bool $sex
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Pets $pet
 */
class ElkPets extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'elk.pets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'ext_id'], 'required'],
            [['id_pet', 'id_elk_owner', 'id_pet_owner', 'id_species', 'id_breed'], 'default', 'value' => null],
            [['id_pet', 'id_elk_owner', 'id_pet_owner', 'id_species', 'id_breed'], 'integer'],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['sex'], 'string', 'max' => 1],
            [['sex'], 'in', 'range' => ['m', 'f'], 'strict' => true],
            [['ext_id', 'name', 'chip'], 'string', 'max' => 255],
            [['ext_id'], 'unique'],
            [['id_elk_owner'], 'exist', 'skipOnError' => true, 'targetClass' => ElkOwners::class, 'targetAttribute' => ['id_elk_owner' => 'id']],
            [['id_breed'], 'exist', 'skipOnError' => true, 'targetClass' => Breeds::class, 'targetAttribute' => ['id_breed' => 'id']],
            [['id_pet_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_pet_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['id_species'], 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => ['id_species' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pet' => 'Id Pet',
            'ext_id' => 'Ext ID',
            'id_elk_owner' => 'Id Elk Owner',
            'id_pet_owner' => 'Id Pet Owner',
            'id_species' => 'Id Species',
            'id_breed' => 'Id Breed',
            'name' => 'Nick Name',
            'chip' => 'Chip',
            'birthday' => 'Birth Date',
            'sex' => 'Sex',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return bool
     */
    public function hasVisits()
    {
        return Visits::find()->where(['id_pet' => $this->id_pet])->exists();
    }

    /**
     * @return bool
     */
    public function hasViolations()
    {
        return Violation::find()->where(['id_pet' => $this->id_pet])->exists();
    }

    /**
     * @return bool
     */
    public function hasAnothePetLinks()
    {
        return ElkPets::find()
            ->where(['id_pet' => $this->id_pet])
            ->andWhere(['!=', 'id', $this->id])
            ->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_pet_owner']);
    }
}
