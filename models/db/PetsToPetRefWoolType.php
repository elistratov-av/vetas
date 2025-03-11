<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_wool_type".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_wool_type
 *
 * @property PetRefWoolType $petRefWoolType
 * @property Pets $pets
 */
class PetsToPetRefWoolType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_wool_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_wool_type'], 'required'],
            [['id_pets', 'id_pet_ref_wool_type'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_wool_type'], 'integer'],
            [['id_pets', 'id_pet_ref_wool_type'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_wool_type']],
            [['id_pet_ref_wool_type'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefWoolType::className(), 'targetAttribute' => ['id_pet_ref_wool_type' => 'id']],
            [['id_pets'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::className(), 'targetAttribute' => ['id_pets' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pets' => 'Id Pets',
            'id_pet_ref_wool_type' => 'Id Pet Ref Wool Type',
        ];
    }

    /**
     * Gets query for [[PetRefWoolType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefWoolType()
    {
        return $this->hasOne(PetRefWoolType::className(), ['id' => 'id_pet_ref_wool_type']);
    }

    /**
     * Gets query for [[Pets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasOne(Pets::className(), ['id' => 'id_pets']);
    }
}
