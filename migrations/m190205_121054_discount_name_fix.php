<?php

use app\commands\migrate\Migration;

/**
 * Class m190205_121054_discount_name_fix
 */
class m190205_121054_discount_name_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('discount','name', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('discount','name', $this->char(255));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190205_121054_discount_name_fix cannot be reverted.\n";

        return false;
    }
    */
}
