<?php

namespace app\models\db;

use app\modules\v2\modules\timesheet\event\TimesheetEventHandler;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "public.timesheets".
 *
 * @property integer                  $id
 * @property string                   $date
 * @property integer                  $id_specialist
 * @property integer                  $id_shift
 * @property integer                  $created_by
 * @property integer                  $updated_by
 * @property string                   $created_at
 * @property string                   $updated_at
 * @property integer                  $parent_id
 * @property integer|null             $vaccination_station_id
 *
 * @property-read  Organizations      $organization
 * @property-read  VaccinationStation $vaccinationStation
 * @property-read  Shifts             $shift
 */
class Timesheets extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.timesheets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date', 'times', 'created_at', 'updated_at'], 'safe'],
            [['id_specialist', 'id_shift', 'created_by', 'updated_by', 'parent_id', 'cabinet_type'], 'integer'],
            [['date', 'times'], 'string'],
            [
                ['id_shift'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Shifts::class,
                'targetAttribute' => ['id_shift' => 'id']
            ],
            [
                ['id_specialist'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Specialists::class,
                'targetAttribute' => ['id_specialist' => 'id']
            ],
            [
                ['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Timesheets::class,
                'targetAttribute' => ['parent_id' => 'id']
            ],
            [
                ['cabinet_type'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CabinetTypes::class,
                'targetAttribute' => ['cabinet_type' => 'id']
            ],
            ['vaccination_station_id', 'default', 'value' => null],
            [
                'vaccination_station_id',
                'exist',
                'targetClass' => VaccinationStation::class,
                'targetAttribute' => 'id',
                'skipOnError' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'id_specialist' => 'Id Specialist',
            'id_shift' => 'Id Shift',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'parent_id' => 'Parent ID',
            'vaccination_station_id' => 'Vaccination Station ID',
            'times' => 'Times',
            'cabinet_type' => 'Cabinet Type',
        ];
    }

    public function getShift(): ActiveQuery
    {
        return $this->hasOne(Shifts::class, ['id' => 'id_shift']);
    }

    public function getShifts_type(): ActiveQuery
    {
        return $this
            ->hasOne(ShiftType::class, ['id' => 'id_type'])
            ->viaTable('shifts', ['id' => 'id_shift']);
    }

    public function getOrganization(): ActiveQuery
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization'])
            ->viaTable('shifts', ['id' => 'id_shift']);
    }

    /**
     * Обертка deleteAll для логирования удалений
     *
     * @param string|array $condition
     * @param array        $params
     *
     * @return int
     */
    public static function deleteAll($condition = '', $params = []): int
    {
        $timesheets = self::find()->where($condition)->all();

        if (empty($timesheets)) {
            return 0;
        }

        TimesheetEventHandler::handleDeleteMany($timesheets);

        return parent::deleteAll($condition, $params);
    }

    public function getVaccinationStation(): ActiveQuery
    {
        return $this->hasOne(VaccinationStation::class, ['id' => 'vaccination_station_id']);
    }
}
