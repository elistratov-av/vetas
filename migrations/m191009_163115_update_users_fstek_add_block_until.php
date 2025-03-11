<?php

use app\commands\migrate\Migration;

/**
 * Class m191009_163115_update_users_fstek_add_block_until
 */
class m191009_163115_update_users_fstek_add_block_until extends Migration
{
    private $tableName = 'admin.users_fstek';

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
