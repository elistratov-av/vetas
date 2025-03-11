<?php

use app\commands\migrate\Migration;

/**
 * Class m210516_134503_add_vaccination_station_id_to_shifts_table
 */
class m210516_134503_add_vaccination_station_id_to_shifts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.shifts', 'vaccination_station_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.shifts', 'vaccination_station_id');
    }
}
