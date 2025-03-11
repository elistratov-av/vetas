<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_pet_ref_skill".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_pet_ref_skill
 *
 * @property PetRefSkill $petRefSkill
 * @property Pets $pets
 */
class PetsToPetRefSkill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_pet_ref_skill'], 'required'],
            [['id_pets', 'id_pet_ref_skill'], 'default', 'value' => null],
            [['id_pets', 'id_pet_ref_skill'], 'integer'],
            [['id_pets', 'id_pet_ref_skill'], 'unique', 'targetAttribute' => ['id_pets', 'id_pet_ref_skill']],
            [['id_pet_ref_skill'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefSkill::className(), 'targetAttribute' => ['id_pet_ref_skill' => 'id']],
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
            'id_pet_ref_skill' => 'Id Pet Ref Skill',
        ];
    }

    /**
     * Gets query for [[PetRefSkill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefSkill()
    {
        return $this->hasOne(PetRefSkill::className(), ['id' => 'id_pet_ref_skill']);
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
