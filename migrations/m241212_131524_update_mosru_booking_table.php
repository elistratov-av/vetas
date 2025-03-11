<?php

use app\commands\migrate\Migration;

/**
 * Class m241212_131524_update_mosru_booking_table
 */
class m241212_131524_update_mosru_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('mosru.booking', 'ext_id', $this->string(255)->null());
        $this->addColumn('mosru.booking', 'final', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('mosru.booking', 'ext_id');
        $this->dropColumn('mosru.booking', 'final');
    }

}
