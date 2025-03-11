<?php

use app\commands\migrate\Migration;

/**
 * Class m230313_103812_change_payment_request_date_column_to_datetime
 */
class m230313_103812_change_payment_request_date_column_to_datetime extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('visits', 'payment_request_date');
        $this->addColumn('visits', 'payment_request_date', $this->dateTime());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'payment_request_date');
        $this->addColumn('visits', 'payment_request_date', $this->date());

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230313_103812_change_payment_request_date_column_to_datetime cannot be reverted.\n";

        return false;
    }
    */
}
