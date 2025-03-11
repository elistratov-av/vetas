<?php

namespace app\models\db;

use app\models\db\tmc\Category;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "public.gov_services".
 *
 * @property integer $id
 * @property integer $id_pricelist
 * @property string $name
 * @property string $price
 * @property integer $sort_by
 * @property integer $id_service_type
 * @property integer $duration
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $alternative_name
 * @property integer $id_service_measure
 * @property string $cod
 * @property integer $cooldown
 * @property boolean $at_home
 * @property boolean $at_clinic
 * @property boolean $deleted
 * @property string $type
 * @property string $briefname              Сокращенное наименование услуги
 * @property string $com_class_journal      Классификация услуги для журналов ('medicalAssistance' | 'additional')
 * @property string $for_broods             Флаг: Для выводков
 * @property string $for_multiple           Флаг: Для нескольких
 * @property bool $once_per_day
 *
 * @property ServiceMeasures $serviceMeasures
 * @property ServiceTypes $serviceType
 * @property GovServicesParams $outParams
 * @property Category[] $categories
 */
class GovServices extends ActiveRecord
{

    /**
     * Возможные значения типа для услуги при ее создании
     * Сохраняется в полях: for_broods, for_multiple
     */
    const FOR_NULL = null;      //персональная
    const FOR_HEAD = 'HEAD';    //на голову
    const FOR_ALL = 'ALL';      //на всех

    CONST
        TYPE_MOSRU = 'mosru'
    ;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.gov_services';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_pricelist', 'name', 'price', 'id_service_type', 'duration'], 'required'],
            [['id_pricelist', 'sort_by', 'id_service_type', 'duration', 'created_by', 'updated_by', 'id_service_measure', 'cooldown'], 'integer'],
            [['name'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['duration', 'cooldown'], 'integer', 'min' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['alternative_name'], 'string', 'max' => 100],
            [['id_service_type'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceTypes::class, 'targetAttribute' => ['id_service_type' => 'id']],
            [['id_service_measure'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceMeasures::class, 'targetAttribute' => ['id_service_measure' => 'id']],
            ['cod', 'match', 'pattern' => '#^\d{4}$#'],
            [['at_home', 'at_clinic', 'deleted'], 'boolean'],
            [['briefname', 'com_class_journal'], 'string'],
            ['com_class_journal', 'in', 'range' => ['medicalAssistance', 'additional']],
            [
                /*
                 * VETAIS-2113
                 */
                ['duration'], function ($attribute, $params) {
                $check = $this->duration % 10;

                if ($check !== 0) {
                    $this->addError(
                        $attribute,
                        'Длительность услуги должна быть кратной 10 минутам'
                    );
                }
            }],
            [
                /*
                * VETAIS-2113
                */
                ['cooldown'], function ($attribute, $params) {
                $check = $this->cooldown % 10;

                if ($check !== 0) {
                    $this->addError(
                        $attribute,
                        'Длительность перерыва должна быть кратной 10 минутам'
                    );
                }
            }],
            [['for_broods', 'for_multiple'], 'string'],
            ['once_per_day', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pricelist' => 'Id Pricelist',
            'name' => 'Name',
            'price' => 'Price',
            'sort_by' => 'Sort By',
            'id_service_type' => 'Id Service Type',
            'duration' => 'Duration',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'alternative_name' => 'Alternative Name',
            'id_service_measure' => 'Id Service Measure',
            'cod' => 'Cod',
            'cooldown' => 'Cooldown',
            'at_home' => 'Услуга может быть оказана на дому',
            'at_clinic' => 'Услуга может быть оказана в клинике',
            'deleted' => 'Услуга удалена'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsGovServices()
    {
        return $this->hasMany(VisitsGovServices::class, ['id_service' => GovServices::tableName() . '.id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceMeasures()
    {
        return $this->hasOne(ServiceMeasures::class, ['id' => 'id_service_measure']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceTypes::class, ['id' => 'id_service_type']);
    }

    /**
     * @return bool
     */
    public function hasCountFlag()
    {
        return static::findCountFlag($this->id);
    }

    /**
     * @param int $id_service
     * @return bool
     */
    public static function findCountFlag(int $id_service)
    {
        return (new Query())
            ->from(static::tableName())
            ->where([static::tableName() . '.id' => $id_service])
            ->select(['count_flag'])
            ->leftJoin(
                ServiceMeasures::tableName(),
                ServiceMeasures::tableName() . '.id = id_service_measure'
            )
            ->scalar();
    }

    /**
     * Необходимое количество слотов
     * @param int $count количество этих услуг в приеме
     * @return int
     */
    public function calcCountSlots(int $count = 1)
    {
        $duration = (int)$this->duration * $count + (int)$this->cooldown;

        return (int)ceil((int)$duration / 10);
    }

    /**
     * @return ActiveQuery
     */
    public function getReports()
    {
        return $this->hasMany(Reports::class,['id' => 'id_report'])
            ->viaTable('gov_services_reports',['id_service' => 'id'])->andWhere(['reports.report_type' => 'R']);
    }

    /**
     * Возвращает флаг сущестования отчета:
     *
     * @return bool
     */
    public function hasReports(): bool
    {
        return $this->getReports()->exists() || $this->getOutParams()->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOutParams()
    {
        return $this->hasMany(GovServicesParams::class, ['id_service' => 'id'])
            ->where(['flag_out' => true]);
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'id_category'])
            ->viaTable('tmc.category_to_gov_services', ['id_service' => 'id']);
    }
}
