<?php

use app\commands\migrate\Migration;

/**
 * Class m210526_134503_add_vaccination_station_id_to_visits_table
 */
class m210526_134503_add_vaccination_station_id_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visits', 'vaccination_station_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visits', 'vaccination_station_id');
    }
}
