<?php

use app\commands\migrate\Migration;

/**
 * Class m210712_111843_change_foreign_keys_specialist2vaccination_station
 */
class m210712_111843_change_foreign_keys_specialist2vaccination_station extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('SPECIALIST2VACCINATION_STATION_VACCINATION_STATION_ID',
            'public.specialist2vaccination_station');

        $this->addForeignKey(
            'SPECIALIST2VACCINATION_STATION_VACCINATION_STATION_ID',
            'public.specialist2vaccination_station',
            'vaccination_station_id',
            'public.vaccination_stations',
            'id',
            'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('SPECIALIST2VACCINATION_STATION_VACCINATION_STATION_ID',
            'public.specialist2vaccination_station');

        $this->addForeignKey(
            'SPECIALIST2VACCINATION_STATION_VACCINATION_STATION_ID',
            'public.specialist2vaccination_station',
            'vaccination_station_id',
            'public.vaccination_stations',
            'id');
    }
}
