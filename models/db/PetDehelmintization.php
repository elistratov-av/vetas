<?php

namespace app\models\db;

use app\common\validators\OrgDictionaryValidator;
use app\models\db\tmc\TmcDrug;
use Yii;

/**
 * Дегельминтизация
 *
 * @property int $id
 * @property int $id_pet
 * @property int $id_drug
 * @property string $type_tmc
 * @property string $drug_name Наименование вакцины
 * @property string $producer_name
 * @property string $date Дата вакцинации
 * @property int $id_organization
 * @property int $id_specialist
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property Drugs $drug
 * @property OrgDictionary $organization
 * @property Pets $pet
 * @property Specialists $specialist
 * @property string $valid_until
 */
class PetDehelmintization extends AbstractPetTmc
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_dehelmintization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'id_drug', 'drug_name', 'producer_name', 'date', 'id_organization', 'id_specialist'], 'required'],
            [['id_pet', 'id_drug', 'id_organization', 'id_specialist', 'created_by', 'updated_by', 'valid_until'], 'default', 'value' => null],
            [['id_pet', 'id_drug', 'id_organization', 'id_specialist', 'created_by', 'updated_by'], 'integer'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [
                ['date'],
                'date', 'format' => 'php:Y-m-d',
                'max' => '2050-01-01',
            ],
            [['drug_name', 'producer_name'], 'string', 'max' => 255],
            [['id_drug'], 'exist', 'skipOnError' => true, 'targetClass' => TmcDrug::class, 'targetAttribute' => ['id_drug' => 'id', 'type_tmc' => 'type']],
            [['id_organization'], OrgDictionaryValidator::class],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => OrgDictionary::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['id_specialist'], 'exist', 'skipOnError' => true, 'targetClass' => Specialists::class, 'targetAttribute' => ['id_specialist' => 'id']],
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
            'id_drug'         => 'Id Drug',
            'drug_name'       => 'Наименование вакцины',
            'producer_name'   => 'Producer Name',
            'date'            => 'Дата вакцинации',
            'id_organization' => 'Id Organization',
            'id_specialist'   => 'Id Specialist',
            'created_by'      => 'Created By',
            'updated_by'      => 'Updated By',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
            'valid_until'     => 'Годен до',
        ];
    }

    /**
     * Возвращает название звязанного id поля ТМЦ
     *
     * @return string
     */
    public function getTmcFieldName(): string
    {
        return 'id_drug';
    }

    /**
     * Возвращает значение звязанного id поля ТМЦ
     *
     * @return int
     */
    public function getTmc(): int
    {
        return $this->id_drug;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrug()
    {
        return $this->hasOne(Drugs::class, ['id' => 'id_drug']);
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
}
