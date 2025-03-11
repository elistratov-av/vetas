<?php

use app\commands\migrate\Migration;

/**
 * Class m190123_075805_1356_change_service_type_for_0365
 */
class m190123_075805_1356_change_service_type_for_0365 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('gov_services',['id_service_type' => '10'],['cod' => '0365']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('gov_services',['id_service_type' => '11'],['cod' => '0365']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190123_075805_1356_change_service_type_for_0365 cannot be reverted.\n";

        return false;
    }
    */
}
