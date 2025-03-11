<?php

use app\commands\migrate\Migration;

/**
 * Class m211108_065607_transfer_service_pet_id
 */
class m211108_065607_transfer_service_pet_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211108_065607_transfer_service_pet_id cannot be reverted.\n";

        return false;
    }
    */
}
