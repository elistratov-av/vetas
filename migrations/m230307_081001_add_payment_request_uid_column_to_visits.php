<?php

use app\commands\migrate\Migration;

/**
 * Class m230307_081001_add_payment_request_uid_column_to_visits
 */
class m230307_081001_add_payment_request_uid_column_to_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'payment_request_uid', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'payment_request_uid');
    }
}
