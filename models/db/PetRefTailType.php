<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_ref_tail_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property Pets[] $pets
 * @property PetsToPetRefTailType[] $petsToPetRefTailTypes
 */
class PetRefTailType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_ref_tail_type';
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
        return $this->hasMany(Pets::className(), ['id' => 'id_pets'])->viaTable('pets_to_pet_ref_tail_type', ['id_pet_ref_tail_type' => 'id']);
    }

    /**
     * Gets query for [[PetsToPetRefTailTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToPetRefTailTypes()
    {
        return $this->hasMany(PetsToPetRefTailType::className(), ['id_pet_ref_tail_type' => 'id']);
    }
}
