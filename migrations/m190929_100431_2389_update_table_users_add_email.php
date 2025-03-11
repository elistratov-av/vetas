<?php

use app\commands\migrate\Migration;

/**
 * Class m190929_100431_2389_update_table_users_add_email
 */
class m190929_100431_2389_update_table_users_add_email extends Migration
{
    private $tableName = 'public.users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'email', $this->string());
        $this->addColumn($this->tableName, 'is_temp_password', $this->boolean()->defaultValue(false)->comment('Временный пароль?'));
        $this->addColumn($this->tableName, 'password_valid_till', $this->dateTime(0)->comment('Срок действия пароля'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'email');
        $this->dropColumn($this->tableName, 'is_temp_password');
        $this->dropColumn($this->tableName, 'password_valid_till');
    }
}
