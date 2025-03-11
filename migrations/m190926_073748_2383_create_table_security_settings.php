<?php

use app\commands\migrate\Migration;

/**
 * Class m190926_073748_2383_create_table_security_settings
 */
class m190926_073748_2383_create_table_security_settings extends Migration
{
    private $tableName = 'admin.security_settings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'jwt_token_ttl' => $this->integer(),
            'allowed_login_attempts' => $this->integer(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
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
