<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m180522_072551_create_users_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
          'users',
          [
            'id' => $this->primaryKey(),
            'login' => $this->string()->notNull(),
            'password' => $this->string()->notNull(),
          ]
        );

        $this->createIndex('idx-users-login', 'users', 'login', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
