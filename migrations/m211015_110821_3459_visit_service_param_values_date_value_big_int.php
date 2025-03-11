<?php

use app\commands\migrate\Migration;

/**
 * Class m211015_110821_3459_visit_service_param_values_date_value_big_int
 */
class m211015_110821_3459_visit_service_param_values_date_value_big_int extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            'visit_service_param_values',
            'date_value',
            $this->bigInteger()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(
            'visit_service_param_values',
            'date_value',
            $this->integer()
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211015_110821_3459_visit_service_param_values_date_value_big_int cannot be reverted.\n";

        return false;
    }
    */
}
