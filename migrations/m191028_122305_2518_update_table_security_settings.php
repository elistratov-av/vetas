<?php

use app\commands\migrate\Migration;

/**
 * Class m191028_122305_2518_update_table_security_settings
 */
class m191028_122305_2518_update_table_security_settings extends Migration
{
    private $tableName = 'admin.security_settings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (['password_duration', 'password_compare_previous'] as $column) {
            $this->addColumn($this->tableName, $column, $this->integer());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['password_duration', 'password_compare_previous'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
