<?php

use app\commands\migrate\Migration;

/**
 * Class m200218_051452_update_asur_task_log_created_at_updated_at
 */
class m200218_051452_update_asur_task_log_created_at_updated_at extends Migration
{
    private $tableName = 'asur.task_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, 'created_at', $this->dateTime(0));
        $this->alterColumn($this->tableName, 'updated_at', $this->dateTime(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200218_051452_update_asur_task_log_created_at_updated_at cannot be reverted.\n";

        return false;
    }
}
