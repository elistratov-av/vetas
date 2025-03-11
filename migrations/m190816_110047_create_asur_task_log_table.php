<?php

use yii\db\Migration;

/**
 * Handles the creation of table `task_log`.
 */
class m190816_110047_create_asur_task_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('asur.task_log', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'status_code' => $this->integer(),
            'status_note' => $this->string(),
            'created_at' => $this->date(),
            'updated_at' => $this->date()
        ]);
        $this->addForeignKey(
            'fk-asur_task_log-task_id',
            'asur.task_log',
            'task_id',
            'asur.task',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('asur.task_log');
    }
}
