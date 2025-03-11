<?php

use app\commands\migrate\Migration;

/**
 * Class m210516_094503_add_time_fields_to_vaccination_station_table
 */
class m210516_094503_add_time_fields_to_vaccination_station_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.vaccination_stations', 'time_from', $this->string());
        $this->addColumn('public.vaccination_stations', 'time_to', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.vaccination_stations', 'time_from');
        $this->dropColumn('public.vaccination_stations', 'time_to');
    }
}
