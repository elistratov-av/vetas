<?php

namespace app\models\db;

use app\common\components\rbac\Role;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * Class VaccinationStation
 *
 * @property int                                   $id
 * @property string|null                           $name
 * @property string|null                           $short_name
 * @property int                                   $parent_id
 * @property string                                $number
 * @property int                                   $kind_vc_id
 * @property string                                $date
 * @property string                                $time_from
 * @property string                                $time_to
 * @property string                                $area_id
 * @property string                                $district_id
 * @property int|null                              $reason_vc_id
 * @property int                                   $fias_address_id
 * @property int|null                              $created_at
 * @property int|null                              $updated_at
 * @property int|null                              $created_by
 * @property int|null                              $updated_by
 * @property string|null                           $address_comment
 *
 * @property-read  Organizations                   $parent
 * @property-read  KindVc                          $kindVc
 * @property-read  ReasonVc                        $reasonVc
 * @property-read  FiasAddresses                   $fiasAddress
 * @property-read  Users                           $createdBy
 * @property-read  Users                           $updatedBy
 * @property-read  Shifts[]                        $shifts
 * @property-read  Specialists[]                   $specialists
 * @property-read  Specialist2VaccinationStation[] $specialist2VaccinationStation
 * @property-read  Visits                          $lastVisit
 *
 * @package app\models\db
 */
class VaccinationStation extends ActiveRecord
{
    public $canEdit;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'public.vaccination_stations';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'name',
                    'short_name',
                    'number',
                    'time_from',
                    'time_to',
                    'area_id',
                    'district_id',
                    'address_comment',
                ],
                'string',
                'max' => 255
            ],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [
                [
                    'parent_id',
                    'number',
                    'kind_vc_id',
                    'date',
                    'time_from',
                    'time_to',
                    'area_id',
                    'district_id',
                    'fias_address_id',
                ],
                'required'
            ],
            [
                [
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                ],
                'safe'
            ],
            [
                [
                    'name',
                    'short_name',
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'parent_id',
                    'kind_vc_id',
                    'reason_vc_id',
                    'fias_address_id',
                ],
                'integer'
            ],
            [
                ['parent_id'],
                'exist',
                'targetClass' => Organizations::class,
                'targetAttribute' => ['parent_id' => 'id']
            ],
            [
                ['kind_vc_id'],
                'exist',
                'targetClass' => KindVc::class,
                'targetAttribute' => ['kind_vc_id' => 'id']
            ],
            [
                ['reason_vc_id'],
                'exist',
                'targetClass' => ReasonVc::class,
                'targetAttribute' => ['reason_vc_id' => 'id']
            ],
            [
                ['fias_address_id'],
                'exist',
                'targetClass' => FiasAddresses::class,
                'targetAttribute' => ['fias_address_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'short_name' => 'Краткое наименование',
            'parent_id' => 'Родительская организация',
            'number' => 'Номер прививочного пункта',
            'kind_vc_id' => 'Вид прививочного пункта',
            'date' => 'Дата проведения вакцинаций',
            'time_from' => 'Часы работы с',
            'time_to' => 'Часы работы по',
            'area_id' => 'Округ',
            'district_id' => 'Район',
            'reason_vc_id' => 'Причина организации',
            'fias_address_id' => 'Адрес',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'created_by' => 'Автор',
            'updated_by' => 'Автор последнего изменения',
            'address_comment' => 'Примечание к адресу',
        ];
    }

    public function getParent(): ActiveQuery
    {
        return $this->hasOne(Organizations::class, ['id' => 'parent_id']);
    }

    public function getShifts(): ActiveQuery
    {
        return $this->hasMany(Shifts::class, ['vaccination_station_id' => 'id']);
    }

    public function getKindVc(): ActiveQuery
    {
        return $this->hasOne(KindVc::class, ['id' => 'kind_vc_id']);
    }

    public function getReasonVc(): ActiveQuery
    {
        return $this->hasOne(ReasonVc::class, ['id' => 'reason_vc_id']);
    }

    public function getFiasAddress(): ActiveQuery
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'fias_address_id']);
    }

    public function getSpecialists(): ActiveQuery
    {
        return $this->hasMany(Specialists::class, ['id' => 'specialist_id'])
            ->viaTable('specialist2vaccination_station', ['vaccination_station_id' => 'id']);
    }

    public function getSpecialist2VaccinationStation(): ActiveQuery
    {
        return $this->hasMany(Specialist2VaccinationStation::class, ['vaccination_station_id' => 'id']);
    }

    public function getLastVisit(): ActiveQuery
    {
        return $this->hasOne(Visits::class, ['vaccination_station_id' => 'id'])
            ->orderBy(['start_dttm' => SORT_DESC]);
    }

    public function getCanEdit(): bool
    {
        if (Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            return true;
        } elseif (Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)) {
            if ($this->parent_id == Yii::$app->user->identity->specialist->id_organization) {
                return true;
            }
        }

        return false;
    }
}
