<?php

use app\commands\migrate\Migration;

/**
 * Class m250109_101419_update_booking_table
 */
class m250109_101419_update_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('mosru.booking', 'final');
        $this->dropColumn('mosru.booking', 'ext_id');
        $this->dropColumn('mosru.booking', 'service_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('mosru.booking', 'service_number', $this->string()->notNull());
        $this->addColumn('mosru.booking', 'ext_id', $this->string(255)->null());
        $this->addColumn('mosru.booking', 'final', $this->boolean()->defaultValue(false));
    }
}
