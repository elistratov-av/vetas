<?php

use app\commands\migrate\Migration;

/**
 * Class m181107_105151_1081_update_service_measure_count_flag
 */
class m181107_105151_1081_update_service_measure_count_flag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('service_measures',['count_flag' => false],['name' => 'исследование']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('service_measures',['count_flag' => true],['name' => 'исследование']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181107_105151_1081_update_service_measure_count_flag cannot be reverted.\n";

        return false;
    }
    */
}
