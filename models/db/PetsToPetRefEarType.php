<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_ear_type".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_ear_type
 *
 * @property PetRefEarType $petRefEarType
 * @property Pets $pets
 */
class PetsToPetRefEarType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_ear_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_ear_type'], 'required'],
            [['id_pets', 'id_pet_ref_ear_type'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_ear_type'], 'integer'],
            [['id_pets', 'id_pet_ref_ear_type'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_ear_type']],
            [['id_pet_ref_ear_type'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefEarType::className(), 'targetAttribute' => ['id_pet_ref_ear_type' => 'id']],
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
            'id_pet_ref_ear_type' => 'Id Pet Ref Ear Type',
        ];
    }

    /**
     * Gets query for [[PetRefEarType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefEarType()
    {
        return $this->hasOne(PetRefEarType::className(), ['id' => 'id_pet_ref_ear_type']);
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
