<?php

namespace app\models\db;

/**
 * @property int $id
 * @property int $id_visit_service_tmc id услуги ТМЦ
 * @property string|null $batch Номер партии/серии
 * @property string|null $production_date Дата изготовления
 * @property string|null $expiry_date Срок годности
 * @property string $date Дата вакцинации
 * @property string|null $valid_until Действительно до
 * @property VisitServiceTmc $visitServiceTmc
 */
class VisitServiceVaccination extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visit_service_vaccination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit_service_tmc', 'date'], 'required'],
            [['id_visit_service_tmc'], 'default', 'value' => null],
            [['id_visit_service_tmc'], 'integer'],
            [['production_date', 'expiry_date', 'date', 'valid_until'], 'safe'],
            [['batch'], 'string', 'max' => 255],
            [['id_visit_service_tmc'], 'unique'],
            [['id_visit_service_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => VisitServiceTmc::className(), 'targetAttribute' => ['id_visit_service_tmc' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'id_visit_service_tmc' => 'id услуги ТМЦ',
            'batch'                => 'Номер партии/серии',
            'production_date'      => 'Дата изготовления',
            'expiry_date'          => 'Срок годности',
            'date'                 => 'Дата вакцинации',
            'valid_until'          => 'Действительно до',
        ];
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
}
