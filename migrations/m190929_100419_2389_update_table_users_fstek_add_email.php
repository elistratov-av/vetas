<?php

use app\commands\migrate\Migration;

/**
 * Class m190929_100419_2389_update_table_users_fstek_add_email
 */
class m190929_100419_2389_update_table_users_fstek_add_email extends Migration
{
    private $tableName = 'admin.users_fstek';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'created_by', $this->integer());
        $this->addColumn($this->tableName, 'updated_by', $this->integer());
        $this->addColumn($this->tableName, 'email', $this->string());
        $this->addColumn($this->tableName, 'is_temp_password', $this->boolean()->defaultValue(false)->comment('Временный пароль?'));
        $this->addColumn($this->tableName, 'password_valid_till', $this->dateTime(0)->comment('Срок действия пароля'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'created_by');
        $this->dropColumn($this->tableName, 'updated_by');
        $this->dropColumn($this->tableName, 'email');
        $this->dropColumn($this->tableName, 'is_temp_password');
        $this->dropColumn($this->tableName, 'password_valid_till');
    }
}
