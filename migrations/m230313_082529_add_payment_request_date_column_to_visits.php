<?php

use app\commands\migrate\Migration;

/**
 * Class m230313_082529_add_payment_request_date_column_to_visits
 */
class m230313_082529_add_payment_request_date_column_to_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'payment_request_date', $this->date());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'payment_request_date');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230313_082529_add_payment_request_date_column_to_visits cannot be reverted.\n";

        return false;
    }
    */
}
