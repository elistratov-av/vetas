<?php

use yii\db\Migration;

/**
 * Class m180814_110249_update_users_table_for_blocking
 */
class m180814_110249_update_users_table_for_blocking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'is_blocked', $this->boolean());
        $this->addColumn('users', 'last_login', $this->datetime());
        $this->addColumn('users', 'block_until', $this->datetime());
        
        $this->createTable('login_attempts', [
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
        $this->dropColumn('users', 'is_blocked');
        $this->dropColumn('users', 'last_login');
        $this->dropColumn('users', 'block_until');
        $this->dropTable('login_attempts');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180814_110249_update_users_table_for_blocking cannot be reverted.\n";

        return false;
    }
    */
}
