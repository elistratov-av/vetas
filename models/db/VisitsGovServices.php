<?php

namespace app\models\db;

use Yii;
use app\models\db\tmc\BalanceFlow;

/**
 * This is the model class for table "public.visits_gov_services".
 *
 * @property integer $id_visit
 * @property integer $id_service
 * @property integer $count
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $id
 * @property bool $apply_discount       Применена скидка
 * @property bool $apply_night_discount Применен ночной тариф
 * @property string $price                Цена без скидок
 * @property string $price_with_discount  Цена со скидками
 * @property integer $id_pet
 * @property VisitServiceParamValues $visitServiceParamValues
 * @property GovServices $service
 * @property Visits $visit
 * @property BalanceFlow[] $balanceFlow
 * @property Pets $pet
 * @property Pets[] $pets
 * @property VisitServiceTmc[] $visitServiceTmc
 * @property VisitServiceTmcPet[] $visitServiceTmcPet
 */
class VisitsGovServices extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.visits_gov_services';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_service'], 'required'],
            [['id_visit', 'id_service', 'count', 'created_by', 'updated_by', 'id_pet'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['apply_discount', 'apply_night_discount'], 'boolean'],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::className(), 'targetAttribute' => ['id_service' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::className(), 'targetAttribute' => ['id_visit' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['price', 'price_with_discount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_visit'             => 'Id Visit',
            'id_service'           => 'Id Service',
            'count'                => 'Count',
            'created_by'           => 'Created By',
            'updated_by'           => 'Updated By',
            'created_at'           => 'Created At',
            'updated_at'           => 'Updated At',
            'id'                   => 'ID',
            'apply_discount'       => 'Применена скидка',
            'apply_night_discount' => 'Применен ночной тариф',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceParamValues()
    {
        return $this->hasMany(VisitServiceParamValues::class, ['id_visitservice' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(GovServices::class, ['id' => 'id_service']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceTypes()
    {
        return $this->hasMany(ServiceTypes::class, ['id' => 'id_service_type'])
            ->viaTable('gov_services', ['id' => 'id_service']);
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
    public function getBalanceFlow()
    {
        return $this->hasMany(BalanceFlow::class, ['id_visit_service' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id' => 'id_pet'])
            ->viaTable('visit_service_pet', ['id_visits_gov_service' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmc()
    {
        return $this->hasMany(VisitServiceTmc::class, ['id_visits_gov_service' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmcPet()
    {
        return $this->hasMany(VisitServiceTmcPet::class, ['id_visits_gov_service' => 'id']);
    }

    /**
     * @param string $name
     * @return array|mixed|null
     */
    public function __get($name)
    {
        if ($name == 'pets') {
            $pets = parent::__get($name);

            if ($pets instanceof Pets) {
                return [$pets];
            }
        }

        return parent::__get($name);
    }
}
