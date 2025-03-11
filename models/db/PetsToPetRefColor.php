<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_color".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_color
 *
 * @property PetRefColor $petRefColor
 * @property Pets $pets
 */
class PetsToPetRefColor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_color';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_color'], 'required'],
            [['id_pets', 'id_pet_ref_color'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_color'], 'integer'],
            [['id_pets', 'id_pet_ref_color'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_color']],
            [['id_pet_ref_color'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefColor::className(), 'targetAttribute' => ['id_pet_ref_color' => 'id']],
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
            'id_pet_ref_color' => 'Id Pet Ref Color',
        ];
    }

    /**
     * Gets query for [[PetRefColor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefColor()
    {
        return $this->hasOne(PetRefColor::className(), ['id' => 'id_pet_ref_color']);
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
