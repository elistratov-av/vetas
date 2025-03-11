<?php

use app\commands\migrate\Migration;

/**
 * Class m191105_091834_update_security_settings_passwords
 */
class m191105_091834_update_security_settings_passwords extends Migration
{
    private $tableName = 'admin.security_settings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (['password_min_duration', 'password_min_changed_symbols'] as $column) {
            $this->addColumn($this->tableName, $column, $this->integer());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['password_min_duration', 'password_min_changed_symbols'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
