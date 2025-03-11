<?php

namespace app\models\db;

use app\common\validators\OrgDictionaryValidator;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcVaccine;
use Yii;

/**
 * Другие вакцинации
 *
 * @property int $id
 * @property int $id_pet
 * @property int $id_vaccine
 * @property int $id_visit_service_tmc
 * @property string $type_tmc
 * @property string $drug_name Наименование вакцины
 * @property string $producer_name Производитель
 * @property string $batch Номер партии/серии
 * @property string $production_date Дата изготовления
 * @property string $expiry_date Срок годности
 * @property string $date Дата вакцинации
 * @property string $valid_until Действительно до
 * @property int $id_organization
 * @property int $id_specialist
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property OrgDictionary $organization
 * @property boolean $is_out_org
 * @property Pets $pet
 * @property Specialists $specialist
 * @property TmcVaccine $vaccine
 * @property VisitServiceTmc $visitServiceTmc
 */
class PetOtherVaccinations extends AbstractPetTmc
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_other_vaccinations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_tmc'], 'required'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'],
                'in',
                'range'       => [
                    TmcBase::TYPE_VACCINE
                ],
                'strict'      => true,
                'skipOnEmpty' => false,
                'skipOnError' => false
            ],

            // Ранее были добавлены как required поля 'id_vaccine', 'id_organization', 'id_specialist', удалены для реализаци функционала суперсервиса в части, касающейся подтверждения доктором вакцинации животного
            [['id_pet', 'drug_name', 'producer_name', 'date'], 'required'],
            [['id_pet', 'id_vaccine', 'id_organization', 'id_specialist', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_pet', 'id_vaccine', 'id_organization', 'id_specialist', 'id_visit_service_tmc', 'created_by', 'updated_by'], 'integer'],
            [['production_date', 'expiry_date', 'date', 'valid_until', 'created_at', 'updated_at'], 'safe'],
            [
                ['production_date', 'expiry_date', 'date', 'valid_until'],
                'date', 'format' => 'php:Y-m-d',
                'max' => '2050-01-01',
                'min' => '2010-01-01',
            ],
            [['is_out_org'], 'boolean'],
            [['drug_name', 'producer_name', 'batch'], 'string', 'max' => 255],
            [['id_organization'], OrgDictionaryValidator::class],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => OrgDictionary::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Specialists::class, 'targetAttribute' => ['id_specialist' => 'id']],
            [['id_vaccine'], 'exist', 'skipOnError' => true, 'targetClass' => TmcVaccine::class, 'targetAttribute' => ['id_vaccine' => 'id', 'type_tmc' => 'type']],
            [['id_visit_service_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => VisitServiceTmc::class, 'targetAttribute' => ['id_visit_service_tmc' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'id_pet'          => 'Id Pet',
            'id_vaccine'      => 'Id Vaccine',
            'type_tmc'        => 'Type tmc',
            'drug_name'       => 'Наименование вакцины',
            'producer_name'   => 'Производитель',
            'batch'           => 'Номер партии/серии',
            'production_date' => 'Дата изготовления',
            'expiry_date'     => 'Срок годности',
            'date'            => 'Дата вакцинации',
            'valid_until'     => 'Действительно до',
            'id_organization' => 'Id Organization',
            'id_specialist'   => 'Id Specialist',
            'created_by'      => 'Created By',
            'updated_by'      => 'Updated By',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    /**
     * Возвращает название звязанного id поля ТМЦ
     *
     * @return string
     */
    public function getTmcFieldName(): string
    {
        return 'id_vaccine';
    }

    /**
     * Возвращает значение звязанного id поля ТМЦ
     *
     * @return int
     */
    public function getTmc(): int
    {
        return $this->id_vaccine;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(OrgDictionary::class, ['id' => 'id_organization', 'is_out_org' => 'is_out_org']);
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
    public function getSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_specialist']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccine()
    {
        return $this->hasOne(TmcVaccine::class, ['id' => 'id_vaccine', 'type' => 'type_tmc']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmc()
    {
        return $this->hasOne(VisitServiceTmc::class, ['id' => 'id_visit_service_tmc']);
    }
}
