<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_tail_type".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_tail_type
 *
 * @property PetRefTailType $petRefTailType
 * @property Pets $pets
 */
class PetsToPetRefTailType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_tail_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_tail_type'], 'required'],
            [['id_pets', 'id_pet_ref_tail_type'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_tail_type'], 'integer'],
            [['id_pets', 'id_pet_ref_tail_type'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_tail_type']],
            [['id_pet_ref_tail_type'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefTailType::className(), 'targetAttribute' => ['id_pet_ref_tail_type' => 'id']],
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
            'id_pet_ref_tail_type' => 'Id Pet Ref Tail Type',
        ];
    }

    /**
     * Gets query for [[PetRefTailType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefTailType()
    {
        return $this->hasOne(PetRefTailType::className(), ['id' => 'id_pet_ref_tail_type']);
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
