<?php


namespace app\models\db;

/**
 * This is the model class for table "diseases_dosages".
 *
 * @property int $id
 * @property int $id_dosage
 * @property int $id_disease
 * */
class DiseasesDosages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'diseases_dosages';
    }

    public function rules()
    {
        return [
            ['id_dosage', 'exist', 'skipOnError' => true, 'targetClass' => Dosages::class, 'targetAttribute' => ['id_dosage' => 'id']],
            ['id_disease', 'exist', 'skipOnError' => true, 'targetClass' => Diseases::class, 'targetAttribute' => ['id_disease' => 'id']],
        ];
    }
}
