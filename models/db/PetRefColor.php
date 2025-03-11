<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_ref_color".
 *
 * @property int $id
 * @property string $title
 *
 * @property Pets[] $pets
 * @property PetsToPetRefColor[] $petsToPetRefColors
 */
class PetRefColor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_ref_color';
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
        return $this->hasMany(Pets::className(), ['id' => 'id_pets'])->viaTable('pets_to_pet_ref_color', ['id_pet_ref_color' => 'id']);
    }

    /**
     * Gets query for [[PetsToPetRefColors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToPetRefColors()
    {
        return $this->hasMany(PetsToPetRefColor::className(), ['id_pet_ref_color' => 'id']);
    }
}
