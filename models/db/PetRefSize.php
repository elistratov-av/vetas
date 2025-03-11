<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_ref_size".
 *
 * @property int $id
 * @property string $title
 *
 * @property Pets[] $pets
 * @property PetsToPetRefSize[] $petsToPetRefSizes
 */
class PetRefSize extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_ref_size';
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
        return $this->hasMany(Pets::className(), ['id' => 'id_pets'])->viaTable('pets_to_pet_ref_size', ['id_pet_ref_size' => 'id']);
    }

    /**
     * Gets query for [[PetsToPetRefSizes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToPetRefSizes()
    {
        return $this->hasMany(PetsToPetRefSize::className(), ['id_pet_ref_size' => 'id']);
    }
}
