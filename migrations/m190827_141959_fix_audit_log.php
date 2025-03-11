<?php

use app\commands\migrate\Migration;
use app\models\db\audit\AuditLog;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m190827_141959_fix_audit_log
 */
class m190827_141959_fix_audit_log extends Migration
{


    public function up()
    {
        $batch_size = 100;

        $preparedQuery = AuditLog::getDb()
            ->createCommand(
                'UPDATE audit.log 
                SET snapshot_generator_version = 2, snapshot = :snapshot
                WHERE id = :id'
            );

        $query = (new Query())
            ->select('*')
            ->from('audit.log')
            ->orderBy('id')
            ->where([
                'AND',
                ['snapshot_generator_version' => 1],
                ['IS NOT', 'snapshot', null],
            ]);

        foreach ($query->batch($batch_size) as $key => $rows) {
            AuditLog::getDb()->beginTransaction();

            Console::output('Process: ' . $batch_size * $key . '-' . $batch_size * ($key + 1));
            foreach ($rows as $row) {
                $fixed_json = json_decode($row['snapshot']);

                $preparedQuery
                    ->bindValue(':snapshot', $fixed_json)
                    ->bindValue(':id', $row['id'])
                    ->execute();
            }

            AuditLog::getDb()->transaction->commit();
        }
    }

    public function down()
    {
        echo "m190827_141959_fix_audit_log cannot be reverted.\n";

        return false;
    }

}
