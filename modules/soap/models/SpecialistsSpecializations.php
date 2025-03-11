<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "specialists_specializations".
 *
 * @property int $id
 * @property int $id_specialist
 * @property int $id_specialization
 */
class SpecialistsSpecializations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specialists_specializations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_specialist', 'id_specialization'], 'default', 'value' => null],
            [['id', 'id_specialist', 'id_specialization'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_specialist' => 'Id Specialist',
            'id_specialization' => 'Id Specialization',
        ];
    }
}
