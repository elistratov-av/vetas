<?php

use app\commands\migrate\Migration;

/**
 * Class m190531_053902_update_visits_gov_services_add_price
 */
class m190531_053902_update_visits_gov_services_add_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits_gov_services', 'price', $this->decimal(8, 2));
        $this->addColumn('visits_gov_services', 'price_with_discount', $this->decimal(8, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits_gov_services', 'price');
        $this->dropColumn('visits_gov_services', 'price_with_discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190531_053902_update_visits_gov_services_add_price cannot be reverted.\n";

        return false;
    }
    */
}
