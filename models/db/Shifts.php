<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "public.shifts".
 *
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $daily
 * @property string                   $from_time
 * @property integer                  $duration
 * @property integer                  $id_organization
 * @property integer                  $created_by
 * @property integer                  $updated_by
 * @property string                   $created_at
 * @property string                   $updated_at
 * @property integer                  $id_type
 * @property integer|null             $vaccination_station_id
 * @property integer                  $shift_type_ref_id
 *
 * @property-read  Timesheets         $timesheet
 * @property-read  ShiftType          $type
 * @property-read  VaccinationStation $vaccinationStation
 */
class Shifts extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.shifts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'from_time', 'id_type'], 'required'],
            [['from_time', 'created_at', 'updated_at'], 'safe'],
            [['duration', 'id_organization', 'created_by', 'updated_by', 'id_type', 'shift_type_ref_id', 'daily'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], FullTrimValidator::class],
            [
                ['name', 'id_organization', 'shift_type_ref_id'],
                'unique',
                'targetAttribute' => ['name', 'id_organization', 'shift_type_ref_id'],
                'message' => 'The combination of Name and Id Organization has already been taken.'
            ],
            [
                ['id_organization'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Organizations::className(),
                'targetAttribute' => ['id_organization' => 'id']
            ],
            [
                ['id_type'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShiftType::className(),
                'targetAttribute' => ['id_type' => 'id']
            ],
            [['duration'], 'integer', 'min' => 1, 'max' => 1440],
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
            'name' => 'Name',
            'daily' => 'Daily',
            'from_time' => 'From Time',
            'duration' => 'Duration',
            'id_organization' => 'Id Organization',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'id_type' => 'Id Type',
            'vaccination_station_id' => 'Vaccination Station ID',
            'shift_type_ref_id' => 'Id Shift Type Ref',
        ];
    }

    public function getTimesheet(): ActiveQuery
    {
        return $this->hasOne(Timesheets::class, ['id_shift' => 'id']);
    }

    public function getType(): ActiveQuery
    {
        return $this->hasOne(ShiftType::class, ['id' => 'id_type']);
    }

    public function getVaccinationStation(): ActiveQuery
    {
        return $this->hasOne(VaccinationStation::class, ['id' => 'vaccination_station_id']);
    }
}
