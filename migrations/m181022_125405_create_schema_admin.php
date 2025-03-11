<?php

use app\commands\migrate\Migration;

/**
 * Class m181022_125405_create_schema_admin
 */
class m181022_125405_create_schema_admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA admin;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA admin;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181022_125405_create_schema_admin cannot be reverted.\n";

        return false;
    }
    */
}
