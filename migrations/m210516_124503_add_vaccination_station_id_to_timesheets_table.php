<?php

use app\commands\migrate\Migration;

/**
 * Class m210516_124503_add_vaccination_station_id_to_timesheets_table
 */
class m210516_124503_add_vaccination_station_id_to_timesheets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.timesheets', 'vaccination_station_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.timesheets', 'vaccination_station_id');
    }
}
