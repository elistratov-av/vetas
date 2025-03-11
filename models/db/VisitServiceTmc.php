<?php

namespace app\models\db;

use app\common\definitions\TmcModelTypeDefinition;
use app\models\db\tmc\Balance;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\TmcBase;

/**
 * This is the model class for table "visit_service_tmc".
 *
 * @property int $id
 * @property int $id_visit ID приема
 * @property int $id_visits_gov_service ID услуги в приеме
 * @property int $id_tmc Ссылка на ТМЦ (если внебалансовое)
 * @property string $type_tmc Ссылка на ТМЦ (если внебалансовое)
 * @property int $id_balance_tmc Ссылка на балансовый ТМЦ
 * @property int $id_dosage ID дозировки (если выбрано списание в дозировке)
 * @property string $count Кол-во высчитанное в стандартных единицах измерения данного ТМЦ
 * @property string $count_selected Значение кол-во введенное пользователем
 * @property string $count_production_form Количество в формах производства
 * @property string $count_utilize Количество утилизорованого ТМЦ
 * @property string $price Высчитанная цена по кол-ву (без скидок или наценок)
 * @property bool $write_off_pack_form Флаг: Списать целиком упаковку (или иную форму производства)
 * @property bool $apply_discount Флаг: применять скидку при выставлении счета
 * @property bool $apply_night_discount Флаг: применять ночной рейт при выставлении счета
 * @property string $created_at Дата создания
 * @property int $created_by Автор добавления
 * @property string $updated_at Дата изменения
 * @property int $updated_by Автор последнего изменения
 * @property Visits $visit
 * @property VisitsGovServices $visitsGovService
 * @property Balance $balance
 * @property Dosages $dosage
 * @property TmcBase $tmc
 * @property TmcBase[] $tmcs
 * @property VisitServiceVaccination $visitServiceVaccination
 * @property PetRabiesVaccination[] $petRabiesVaccination
 * @property PetOtherVaccinations[] $petOtherVaccinations
 * @property VisitServiceTmcPet[] $visitServiceTmcPet
 * @property array $petVaccination
 */
class VisitServiceTmc extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visit_service_tmc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit', 'id_visits_gov_service', 'id_tmc', 'type_tmc'], 'required'],
            [['id_visit', 'id_visits_gov_service', 'id_tmc', 'id_balance_tmc', 'id_dosage', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_visit', 'id_visits_gov_service', 'id_tmc', 'id_balance_tmc', 'id_dosage', 'created_by', 'updated_by'], 'integer'],
            [['type_tmc'], 'string'],
            [['count', 'count_selected', 'count_production_form', 'count_utilize', 'price'], 'number'],
            [['write_off_pack_form', 'apply_discount', 'apply_night_discount'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::class, 'targetAttribute' => ['id_visit' => 'id']],
            [['id_visits_gov_service'], 'exist', 'skipOnError' => true, 'targetClass' => VisitsGovServices::class, 'targetAttribute' => ['id_visits_gov_service' => 'id']],
            [
                ['id_balance_tmc'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Balance::class,
                'targetAttribute' => ['id_tmc' => 'id_tmc', 'type_tmc' => 'type_tmc', 'id_balance_tmc' => 'id']
            ],
            [['id_dosage'], 'exist', 'skipOnError' => true, 'targetClass' => Dosages::class, 'targetAttribute' => ['id_dosage' => 'id']],
            [['id_tmc', 'type_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => TmcBase::class, 'targetAttribute' => ['id_tmc' => 'id', 'type_tmc' => 'type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'id_visit'              => 'ID приема',
            'id_visits_gov_service' => 'ID услуги в приеме',
            'id_tmc'                => 'Ссылка на ТМЦ (если внебалансовое)',
            'type_tmc'              => 'Ссылка на ТМЦ (если внебалансовое)',
            'id_balance_tmc'        => 'Ссылка на балансовый ТМЦ',
            'id_dosage'             => 'ID дозировки (если выбрано списание в дозировке)',
            'count'                 => 'Кол-во высчитанное в стандартных единицах измерения данного ТМЦ',
            'count_selected'        => 'Значение кол-во введенное пользователем',
            'count_production_form' => 'Количество в формах производства',
            'count_utilize'         => 'Количество утилизорованого ТМЦ',
            'price'                 => 'Высчитанная цена по кол-ву (без скидок или наценок)',
            'write_off_pack_form'   => 'Флаг: Списать целиком упаковку (или иную форму производства)',
            'apply_discount'        => 'Флаг: применять скидку при выставлении счета',
            'apply_night_discount'  => 'Флаг: применять ночной рейт при выставлении счета',
            'created_at'            => 'Дата создания',
            'created_by'            => 'Автор добавления',
            'updated_at'            => 'Дата изменения',
            'updated_by'            => 'Автор последнего изменения',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalance()
    {
        return $this->hasOne(Balance::class, ['id' => 'id_balance_tmc']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDosage()
    {
        return $this->hasOne(Dosages::class, ['id' => 'id_dosage']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmc()
    {
        $class = $this->type_tmc ? TmcModelTypeDefinition::getModelByType($this->type_tmc) : TmcBase::class;

        return $this->hasOne($class, ['id' => 'id_tmc', 'type' => 'type_tmc']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmcs()
    {
        $class = $this->type_tmc ? TmcModelTypeDefinition::getModelByType($this->type_tmc) : TmcBase::class;

        return $this->hasMany($class, ['id' => 'id_tmc', 'type' => 'type_tmc']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetRabiesVaccination()
    {
        return $this->hasMany(PetRabiesVaccination::class, ['id_visit_service_tmc' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOtherVaccination()
    {
        return $this->hasMany(PetOtherVaccinations::class, ['id_visit_service_tmc' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetVaccination()
    {
        if ($this->petRabiesVaccination) {
            return $this->getPetRabiesVaccination();
        } else {
            if ($this->petOtherVaccination) {
                return $this->getPetOtherVaccination();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceVaccination()
    {
        return $this->hasOne(VisitServiceVaccination::class, ['id_visit_service_tmc' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmcPet()
    {
        return $this->hasMany(VisitServiceTmcPet::class, ['id_visit_service_tmc' => 'id']);
    }
}
