<?php

namespace app\models\db;

/**
 * @property int $id
 * @property int $id_visit id приема
 * @property int $id_visits_gov_service id услуги
 * @property int $id_visit_service_tmc id ТМЦ услуги
 * @property int $id_pet id животного
 * @property string|null $type_tmc Тип ТМЦ
 *
 * @property Pets $pet
 * @property Visits $visit
 * @property VisitServiceTmc $visitServiceTmc
 * @property VisitsGovServices $visitsGovService
 */
class VisitServiceTmcPet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.visit_service_tmc_pet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_visits_gov_service', 'id_visit_service_tmc', 'id_pet'], 'required'],
            [['id_visit', 'id_visits_gov_service', 'id_visit_service_tmc', 'id_pet'], 'default', 'value' => null],
            [['id_visit', 'id_visits_gov_service', 'id_visit_service_tmc', 'id_pet'], 'integer'],
            [['type_tmc'], 'string'],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::className(), 'targetAttribute' => ['id_pet' => 'id']],
            [['id_visit_service_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => VisitServiceTmc::className(), 'targetAttribute' => ['id_visit_service_tmc' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::className(), 'targetAttribute' => ['id_visit' => 'id']],
            [['id_visits_gov_service'], 'exist', 'skipOnError' => true, 'targetClass' => VisitsGovServices::className(), 'targetAttribute' => ['id_visits_gov_service' => 'id']],
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
            'id_visits_gov_service' => 'Id Visits Gov Service',
            'id_visit_service_tmc' => 'Id Visit Service Tmc',
            'id_pet' => 'Id Pet',
            'type_tmc' => 'Type Tmc',
        ];
    }

    /**
     * Gets query for [[Pet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::className(), ['id' => 'id_pet']);
    }

    /**
     * Gets query for [[Visit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::className(), ['id' => 'id_visit']);
    }

    /**
     * Gets query for [[VisitServiceTmc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmc()
    {
        return $this->hasOne(VisitServiceTmc::className(), ['id' => 'id_visit_service_tmc']);
    }

    /**
     * Gets query for [[VisitsGovService]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovService()
    {
        return $this->hasOne(VisitsGovServices::className(), ['id' => 'id_visits_gov_service']);
    }
}
