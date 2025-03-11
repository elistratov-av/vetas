<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pets_to_ground_for_disposal".
 *
 * @property int $id
 * @property int $id_pets
 * @property int $id_ground_for_disposal
 *
 * @property PetRefBasisOfDisposal $PetRefBasisOfDisposal
 * @property Pets $pets
 */
class PetsToPetRefBasisOfDisposal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_to_pet_ref_ground_for_disposal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pets', 'id_ground_for_disposal'], 'required'],
            [['id_pets', 'id_ground_for_disposal'], 'default', 'value' => null],
            [['id_pets', 'id_ground_for_disposal'], 'integer'],
            [['id_pets', 'id_ground_for_disposal'], 'unique', 'targetAttribute' => ['id_pets', 'id_ground_for_disposal']],
            [['id_ground_for_disposal'], 'exist', 'skipOnError' => true, 'targetClass' => PetRefBasisOfDisposal::className(), 'targetAttribute' => ['id_ground_for_disposal' => 'id']],
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
            'id_ground_for_disposal' => 'Id Grounds For Disposal',
        ];
    }

    /**
     * Gets query for [[PetRefBasisOfDisposal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetRefBasisOfDisposal()
    {
        return $this->hasOne(PetRefBasisOfDisposal::className(), ['id' => 'id_ground_for_disposal']);
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
