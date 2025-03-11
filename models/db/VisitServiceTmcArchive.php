<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "visit_service_tmc".
 *
 * @property int $id
 * @property int $id_visit
 * @property int $id_visits_gov_service
 * @property int $id_entity
 * @property int $id_measure
 * @property int $id_organization
 * @property string $entity_type
 * @property string $name
 * @property string $inventory_number
 * @property string $measure_name
 * @property int $count
 * @property string $price
 * @property bool $apply_discount
 * @property bool $apply_night_discount
 *
 * @property Visits $visit
 * @property VisitsGovServices $visitsGovService
 */
class VisitServiceTmcArchive extends \yii\db\ActiveRecord
{

    const
        TYPE_BALANCE_EQUIPMENTS = 'balance_equipments',
        TYPE_BALANCE_VACCINES = 'balance_vaccines',
        TYPE_BALANCE_EXP_MATERIALS = 'balance_exp_materials',
        TYPE_BALANCE_DRUGS = 'balance_drugs',
        TYPE_DRUG = 'drugs',
        TYPE_EQUIPMENT = 'equipments',
        TYPE_EXP_MATERIAL = 'exp_materials',
        TYPE_VACCINE = 'vaccines';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visit_service_tmc_archive';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_visits_gov_service', 'id_entity', 'entity_type'], 'required'],
            [['id_visit', 'id_visits_gov_service', 'id_entity', 'id_measure', 'id_organization', 'count'], 'default', 'value' => null],
            [['id_visit', 'id_visits_gov_service', 'id_entity', 'id_measure', 'id_organization'], 'integer'],
            [['entity_type'], 'string'],
            [['entity_type'], 'in', 'range' => [
                self::TYPE_DRUG, self::TYPE_EQUIPMENT, self::TYPE_EXP_MATERIAL, self::TYPE_VACCINE,
                self::TYPE_BALANCE_DRUGS, self::TYPE_BALANCE_EQUIPMENTS, self::TYPE_BALANCE_EXP_MATERIALS, self::TYPE_BALANCE_VACCINES]
            ],
            [
                'count',
                'integer',
                'min' => 1,
                'max' => 9999999,
                'when' => function ($model) {
                    /** @var $model \app\models\db\VisitServiceTmcArchive */
                    return empty($model->entity_type) || in_array($model->entity_type, [$model::TYPE_EQUIPMENT, $model::TYPE_BALANCE_EQUIPMENTS]);
                },
            ],
            [
                'count',
                'number',
                'min' => 0.01,
                'numberPattern' => '/^[0-9]{1,7}\.?[0-9]{0,2}$/',
                'when' => function ($model) {
                    /** @var $model \app\models\db\VisitServiceTmcArchive */
                    return in_array($model->entity_type, [$model::TYPE_DRUG, $model::TYPE_EXP_MATERIAL, $model::TYPE_VACCINE,
                        $model::TYPE_BALANCE_DRUGS, $model::TYPE_BALANCE_EXP_MATERIALS, $model::TYPE_BALANCE_VACCINES]);
                },
            ],
            [['price'], 'number'],
            [['apply_discount', 'apply_night_discount'], 'boolean'],
            [['name', 'inventory_number'], 'string', 'max' => 255],
            [['measure_name'], 'string', 'max' => 50],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::class, 'targetAttribute' => ['id_visit' => 'id']],
            [['id_visits_gov_service'], 'exist', 'skipOnError' => true, 'targetClass' => VisitsGovServices::class, 'targetAttribute' => ['id_visits_gov_service' => 'id']],
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
            'id_entity' => 'Id Entity',
            'id_measure' => 'Id Measure',
            'id_organization' => 'Id Organization',
            'entity_type' => 'Entity Type',
            'name' => 'Name',
            'inventory_number' => 'Inventory Number',
            'measure_name' => 'Measure Name',
            'count' => 'Count',
            'price' => 'Price',
            'apply_discount' => 'Apply Discount',
            'apply_night_discount' => 'Apply Night Discount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovService()
    {
        return $this->hasOne(VisitsGovServices::class, ['id' => 'id_visits_gov_service']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        return false; // Read-only
    }
}
