<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_ref_skill".
 *
 * @property int $id
 * @property string $title
 *
 * @property Pets[] $pets
 * @property PetsToPetRefSkill[] $petsToPetRefSkill
 */
class PetRefSkill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_ref_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    /**
     * Gets query for [[Pets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::className(), ['id' => 'id_pets'])->viaTable('pets_to_pet_ref_skill', ['id_pet_ref_skill' => 'id']);
    }

    /**
     * Gets query for [[PetsToPetRefSkill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToPetRefSkill()
    {
        return $this->hasMany(PetsToPetRefSkill::className(), ['id_pet_ref_skill' => 'id']);
    }
}