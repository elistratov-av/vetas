<?php

use app\commands\migrate\Migration;

/**
 * Class m191010_090938_create_table_log_users_change
 */
class m191010_090938_create_table_log_users_change extends Migration
{
    private $tableName = 'audit.log_users_change';

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
            'before' => $this->json(),
            'after' => $this->json(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
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
