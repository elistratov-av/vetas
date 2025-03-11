<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "ground_for_disposal".
 *
 * @property int $id
 * @property string $title
 *
 * @property Pets[] $pets
 * @property PetsToPetRefGroundForDisposal[] $petsToPetRefGroundForDisposals
 */
class PetRefGroundForDisposal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_ref_ground_for_disposal';
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
        return $this->hasMany(Pets::className(), ['id' => 'id_pets'])->viaTable('pets_to_ground_for_disposal', ['id_ground_for_disposal' => 'id']);
    }

    /**
     * Gets query for [[PetsToPetRefGroundForDisposals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToPetRefGroundForDisposals()
    {
        return $this->hasMany(PetsToPetRefGroundForDisposal::className(), ['id_ground_for_disposal' => 'id']);
    }
}
