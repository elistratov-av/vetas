<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "visits_specialists".
 *
 * @property integer $id_visit
 * @property integer $id_specialist
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Specialists $idSpecialist
 * @property Visits $idVisit
 */
class VisitsSpecialists extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'visits_specialists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_specialist'], 'required'],
            [['id_visit', 'id_specialist', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Specialists::className(), 'targetAttribute' => ['id_specialist' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::className(), 'targetAttribute' => ['id_visit' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_visit' => 'Id Visit',
            'id_specialist' => 'Id Specialist',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdSpecialist()
    {
        return $this->hasOne(Specialists::className(), ['id' => 'id_specialist'])->inverseOf('visitsSpecialists');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdVisit()
    {
        return $this->hasOne(Visits::className(), ['id' => 'id_visit'])->inverseOf('visitsSpecialists');
    }
}
