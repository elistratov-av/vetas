<?php

use app\commands\migrate\Migration;

/**
 * Class m191028_131706_2518_update_table_security_settings_2
 */
class m191028_131706_2518_update_table_security_settings_2 extends Migration
{
    private $tableName = 'admin.security_settings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'password_min_length', $this->integer());
        foreach (['password_contains_letters', 'password_both_case', 'password_contains_digits', 'password_contains_symbols'] as $column) {
            $this->addColumn($this->tableName, $column, $this->boolean()->defaultValue(false));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['password_min_length', 'password_contains_letters', 'password_both_case', 'password_contains_digits', 'password_contains_symbols'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
