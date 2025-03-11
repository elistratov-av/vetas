<?php

namespace app\commands;

use app\models\db\audit\LogsCleanupLog;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;

/**
 * Class LogsCleanupController
 * @package app\commands
 */
class LogsCleanupController extends Controller
{
    /**
     * @var array
     */
    private $logTables = [
        'audit.log' => 'date',
        'audit.log_external_auth' => 'created_at',
        'audit.log_users_access_change' => 'created_at',
        'audit.log_users_change' => 'created_at',
        'audit.log_users_auth' => 'created_at',
        'audit.log_users_block_auto' => 'created_at',
        'audit.log_users_block_manual' => 'created_at',
    ];

    /**
     * Ежеднесная консольная команда для очистки таблиц логов и записи результата в лог:
     * ```
     *  > php yii logs-cleanup/cleanup
     * ```
     *
     * @return int
     * @throws \Exception
     */
    public function actionCleanup()
    {
        $dateTo = (new \DateTime())->modify('-1 year');
        $dateTimeTo = $dateTo->format('Y-m-d') . ' 23:59:59';
        $dateFrom = $dateTo->modify('-1 day');
        $dateTimeFrom = $dateFrom->format('Y-m-d') . ' 00:00:00';

        foreach ($this->logTables as $logTable => $column) {
            $count = (new Query())
                ->from($logTable)
                ->where(['between', $column, $dateTimeFrom, $dateTimeTo])
                ->count();
            if ($count > 0) {
                try {
                    $result = \Yii::$app->db->createCommand()
                        ->delete($logTable, ['between', $column, $dateTimeFrom, $dateTimeTo])
                        ->execute();
                    $this->logJob($logTable, $count, $dateTimeFrom, $dateTimeTo, $result > 0);
                } catch (\Throwable $e) {
                    $this->logJob($logTable, $count, $dateTimeFrom, $dateTimeTo, false);
                }
            } else {
                $this->logJob($logTable, $count, $dateTimeFrom, $dateTimeTo, true);
            }
        }

        return ExitCode::OK;
    }

    /**
     * @param string $logTable
     * @param int    $count
     * @param string $dateTimeFrom
     * @param string $dateTimeTo
     * @param bool   $is_success
     */
    private function logJob($logTable, $count, $dateTimeFrom, $dateTimeTo, $is_success)
    {
        $record = new LogsCleanupLog([
            'table_name' => $logTable,
            'is_success' => $is_success,
            'records_count' => $count,
            'date_from' => $dateTimeFrom,
            'date_to' => $dateTimeTo,
        ]);

        $record->save();
    }
}
