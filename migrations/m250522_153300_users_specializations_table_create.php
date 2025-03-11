<?php

use app\commands\migrate\Migration;

/**
 * Class m250522_153300_users_specializations_table_create
 */
class m250522_153300_users_specializations_table_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.users_specializations', [
            'id' => $this->primaryKey(),
            'id_specialization' => $this->integer()->comment('Ссылка на специализацию'),
            'id_user' => $this->integer()->comment('Ссылка на пользователя'),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);
        // $this->addPrimaryKey('users_specializations_pkey', 'public.users_specializations', ['id_specialization', 'id_user']);
       
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users_specializations');

        return true;
    }
}