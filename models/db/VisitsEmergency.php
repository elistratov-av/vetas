<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "visits_emergency".
 *
 * @property int $id
 * @property int $id_visit Ссылка на прием
 * @property int $id_emergency Ссылка на экстренную ситуацию
 * @property bool $notify_status
 *
 * @property OrganizationsEmergency $emergency
 * @property Visits $visit
 */
class VisitsEmergency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visits_emergency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_emergency'], 'default', 'value' => null],
            [['id_visit', 'id_emergency'], 'integer'],
            [['notify_status'], 'boolean'],
            [['id_emergency'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationsEmergency::className(), 'targetAttribute' => ['id_emergency' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::className(), 'targetAttribute' => ['id_visit' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_visit' => 'Id Visit',
            'id_emergency' => 'Id Emergency',
            'notify_status' => 'Notify Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmergency()
    {
        return $this->hasOne(OrganizationsEmergency::className(), ['id' => 'id_emergency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::className(), ['id' => 'id_visit']);
    }

    /**
     * @param int $emergencyId
     * @param int $visitId
     * @return VisitsEmergency|array|null|\yii\db\ActiveRecord
     */
    public function getVisitEmergencyByEmergencyIdAndByVisitId(int $emergencyId, int $visitId)
    {
        return self::find()
            ->where(['id_visit' => $visitId])
            ->andWhere(['id_emergency' => $emergencyId])
            ->one()
        ;
    }
}
