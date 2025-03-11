<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_size".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_size
 *
 * @property PetRefSize $petRefSize
 * @property Pets $pets
 */
class PetsToPetRefSize extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_size';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_size'], 'required'],
            [['id_pets', 'id_pet_ref_size'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_size'], 'integer'],
            [['id_pets', 'id_pet_ref_size'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_size']],
            [['id_pet_ref_size'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefSize::className(), 'targetAttribute' => ['id_pet_ref_size' => 'id']],
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
            'id_pet_ref_size' => 'Id Pet Ref Size',
        ];
    }

    /**
     * Gets query for [[PetRefSize]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefSize()
    {
        return $this->hasOne(PetRefSize::className(), ['id' => 'id_pet_ref_size']);
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
