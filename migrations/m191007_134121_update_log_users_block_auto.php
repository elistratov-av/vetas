<?php

use app\commands\migrate\Migration;

/**
 * Class m191007_134121_update_log_users_block_auto
 */
class m191007_134121_update_log_users_block_auto extends Migration
{
    private $tableName = 'audit.log_users_block_auto';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'block_until', $this->dateTime(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'block_until');
    }
}
