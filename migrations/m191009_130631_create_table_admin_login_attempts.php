<?php

use app\commands\migrate\Migration;

/**
 * Class m191009_130631_create_table_admin_login_attempts
 */
class m191009_130631_create_table_admin_login_attempts extends Migration
{
    private $tableName = 'admin.login_attempts';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'id_user' => $this->integer()->notNull(),
            'date' => $this->datetime()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
