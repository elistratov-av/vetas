<?php

use app\commands\migrate\Migration;

/**
 * Class m191025_114109_2422_update_table_log_external_auth_add_ip
 */
class m191025_114109_2422_update_table_log_external_auth_add_ip extends Migration
{
    private $tableName = 'audit.log_external_auth';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'ip', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn($this->tableName, 'ip');
    }
}
