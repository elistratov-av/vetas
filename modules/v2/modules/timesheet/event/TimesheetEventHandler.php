<?php

namespace app\modules\v2\modules\timesheet\event;

use app\models\db\audit\TimesheetLog;
use app\models\db\Timesheets;
use Yii;
use yii\db\Expression;

class TimesheetEventHandler
{

    /** Версия генератора слепка */
    private const SNAPSHOT_GENERATOR_VERSION = 1;

    /** Версия API */
    private const API_VERSION = 2;

    /** Перечень столбцов лога для записи в таблицу */
    private const TIMESHEET_LOGS_COLUMNS = [
        'id_timesheet',
        'id_initiator',
        'fio_initiator',
        'id_specialist',
        'snapshot',
        'api_version',
        'snapshot_generator_version',
        'date',
    ];

    /**
     * Логирование удаления расписания(ий)
     *
     * @param Timesheets[] $timesheets
     *
     * @return bool
     */
    public static function handleDeleteMany(array $timesheets): bool
    {
        try {
            $rows = self::prepareTimesheets($timesheets);

            TimesheetLog::getDb()->createCommand()
                ->batchInsert(
                    TimesheetLog::tableName(),
                    self::TIMESHEET_LOGS_COLUMNS,
                    $rows
                )
                ->execute();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Подгатавливает лог к записи
     *
     * @param Timesheets[] $timesheets
     *
     * @return array
     */
    private static function prepareTimesheets(array $timesheets): array
    {
        return array_map(
            static function (Timesheets $timesheet) {
                return [
                    $timesheet->id,
                    Yii::$app->user->identity->getId(),
                    Yii::$app->user->identity->fullname,
                    $timesheet->id_specialist,
                    $timesheet->getAttributes(),
                    self::API_VERSION,
                    self::SNAPSHOT_GENERATOR_VERSION,
                    new Expression('NOW()::timestamp without time zone')
                ];
            },
            $timesheets);
    }
}