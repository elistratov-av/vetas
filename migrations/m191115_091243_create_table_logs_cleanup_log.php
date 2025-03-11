<?php

use app\commands\migrate\Migration;

/**
 * Class m191115_091243_create_table_logs_cleanup_log
 */
class m191115_091243_create_table_logs_cleanup_log extends Migration
{
    private $tableName = 'audit.logs_cleanup_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'table_name' => $this->string(),
            'is_success' => $this->boolean()->notNull(),
            'records_count' => $this->string(),
            'date_from' => $this->dateTime(0),
            'date_to' => $this->dateTime(0),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
