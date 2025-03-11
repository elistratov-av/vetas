<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "audit.timesheets_logs"
 *
 * @property int    $id                         ID
 * @property int    $id_timesheet               ID расписания
 * @property int    $id_initiator               ID инициатора
 * @property int    $fio_initiator              ФИО инициатора
 * @property int    $id_specialist              ID специалиста
 * @property array  $snapshot                   Состояние расписания
 * @property int    $api_version                Версия API
 * @property int    $snapshot_generator_version Версия создателя снимков
 * @property string $date                       Дата и время изменения данных
 */
class TimesheetLog extends ActiveRecord
{

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()::timestamp without time zone'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'audit.timesheets_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id_user', 'fio_user', 'id_organization'], 'default', 'value' => null],
            [['id', 'id_timesheet', 'id_initiator', 'id_specialist', 'api_version', 'snapshot_generator_version'], 'integer'],
            [['date', 'snapshot'], 'safe'],
            [['fio_initiator'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'id_timesheet' => 'ID расписания',
            'id_initiator' => 'ID инициатора',
            'fio_initiator' => 'ФИО инициатора',
            'id_specialist' => 'ID специалиста',
            'snapshot' => 'Состояние расписания',
            'api_version' => 'Версия API',
            'snapshot_generator_version' => 'Версия создателя снимков',
            'date' => 'Дата и время изменения данных',
        ];
    }
}