<?php

namespace app\models\db;

use app\modules\v2\modules\vaccinationJournal\models\FlatModel;

/**
 * Отражает посещения на обходах, по которым на заведены Осмотры (Visits)
 * Текущие кейсы:
 * 1) животное отсуствует на адресе
 * 2) животное в принципе не существует
 * 3) отказ от вакцинации владельцем
 *
 * Class QuarantineDetourNonVisit
 * @package app\models\db
 *
 * @property int $id_quarantine
 * @property int $id_fias_address
 * @property int $id_pet
 * @property int $absent_pet_reason
 * @property string $date
 *
 * @property FiasAddresses $fias_address
 * @property Quarantine $quarantine
 * @property Pets $pet
 */
class QuarantineDetourNonVisit extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'quarantine_detour_non_visit';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_quarantine', 'date', 'absent_pet_reason', 'id_fias_address'], 'required'],
            [['id_quarantine', 'id_pet', 'absent_pet_reason', 'id_fias_address'], 'integer'],
            [['id_quarantine'], 'exist', 'skipOnError' => true, 'targetClass' => Quarantine::class, 'targetAttribute' => ['id_quarantine' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['id_fias_address'], 'exist', 'skipOnError' => true, 'targetClass' => FiasAddresses::class, 'targetAttribute' => ['id_fias_address' => 'id']],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['absent_pet_reason'], 'in', 'range' => $this->absenceReasons()],
            [['id_pet'], 'required', 'when' => function($model) {
                /** @var $model QuarantineDetourNonVisit */
                return in_array($model->absent_pet_reason, $this->absenceReasonsWithPets());
            }],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuarantine()
    {
        return $this->hasOne(Quarantine::class, ['id' => 'id_quarantine']);
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
    public function getFias_address()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return array
     */
    public static function absenceReasons(): array
    {
        return [
            FlatModel::PET_OUT,
            FlatModel::PET_IN_HOSPITAL,
            FlatModel::PET_IS_DEATH,
            FlatModel::LIVING_SPACE_HAS_NO_PET,
            FlatModel::OWNER_REFUSED,
            FlatModel::PET_NOT_EXISTS,
            FlatModel::PET_HAS_OTHER_OWNER,
            FlatModel::PET_HAS_OUTSIDE_ORG_VACCINE,
        ];
    }

    public static function absenceReasonsWithPets(): array
    {
        return [
            FlatModel::PET_OUT,
            FlatModel::PET_IN_HOSPITAL,
            FlatModel::PET_IS_DEATH,
            FlatModel::OWNER_REFUSED,
            FlatModel::PET_HAS_OUTSIDE_ORG_VACCINE,
        ];
    }
}
