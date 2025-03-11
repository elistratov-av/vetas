<?php

use app\commands\migrate\Migration;

/**
 * Class m191007_091049_log_users_block_manual
 */
class m191007_091049_log_users_block_manual extends Migration
{
    private $tableName = 'audit.log_users_block_manual';

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
