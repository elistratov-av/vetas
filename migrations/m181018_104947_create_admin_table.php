<?php

use yii\db\Migration;

/**
 * Handles the creation of table `admin`.
 */
class m181018_104947_create_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('admin', [
            'id' => $this->primaryKey(),
            'login' => $this->string(),
            'password' => $this->string(),
            'last_login' => $this->timestamp(),
            'ip' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('admin');
    }
}
