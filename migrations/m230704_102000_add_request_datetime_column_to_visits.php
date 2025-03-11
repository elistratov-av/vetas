<?php

use app\commands\migrate\Migration;

/**
 * Class m230704_102000_add_request_datetime_column_to_visits
 */
class m230704_102000_add_request_datetime_column_to_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'start_request_date', $this->dateTime());
        $this->addColumn('visits', 'end_request_date', $this->dateTime());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'start_request_date');
        $this->dropColumn('visits', 'end_request_date');

        return false;
    }
}
