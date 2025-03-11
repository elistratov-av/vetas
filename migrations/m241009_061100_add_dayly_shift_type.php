<?php

use app\commands\migrate\Migration;

/**
 * Class m241009_061100_add_dayly_shift_type
 */
class m241009_061100_add_dayly_shift_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.shifts', 'daily', $this->smallInteger()->after('name')->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.shifts', 'daily');
    }
}
