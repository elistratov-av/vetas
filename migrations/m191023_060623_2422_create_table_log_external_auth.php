<?php

use app\commands\migrate\Migration;

/**
 * Class m191023_060623_2422_create_table_log_external_auth
 */
class m191023_060623_2422_create_table_log_external_auth extends Migration
{
    private $tableName = 'audit.log_external_auth';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'service_name' => $this->string(),
            'is_success' => $this->boolean()->notNull(),
            'protocol' => $this->string(),
            'interface' => $this->string(),
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
