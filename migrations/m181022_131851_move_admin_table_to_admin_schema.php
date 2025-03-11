<?php

use app\commands\migrate\Migration;

/**
 * Class m181022_131851_move_admin_table_to_admin_schema
 */
class m181022_131851_move_admin_table_to_admin_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE admin 
                            SET SCHEMA admin;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE admin 
                            SET SCHEMA public;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181022_131851_move_admin_table_to_admin_schema cannot be reverted.\n";

        return false;
    }
    */
}
