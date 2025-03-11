<?php

use app\commands\migrate\Migration;

/**
 * Class m191003_120343_2418_create_table_log_users_auth
 */
class m191003_120343_2418_create_table_log_users_auth extends Migration
{
    private $tableName = 'audit.log_users_auth';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'login' => $this->string(),
            'id_user' => $this->integer(),
            'target' => $this->tinyInteger(1)->notNull(),
            'type' => $this->tinyInteger(1)->notNull(),
            'is_success' => $this->boolean()->notNull(),
            'ip' => $this->string(),
            'ua' => $this->string(1000),
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
