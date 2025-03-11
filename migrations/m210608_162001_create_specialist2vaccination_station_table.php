<?php

use app\commands\migrate\Migration;

/**
 * Class m210608_162001_create_specialist2vaccination_station_table
 */
class m210608_162001_create_specialist2vaccination_station_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.specialist2vaccination_station', [
            'id' => $this->bigPrimaryKey(),
            'specialist_id' => $this->bigInteger()->unsigned(),
            'organization_id' => $this->bigInteger()->unsigned(),
            'vaccination_station_id' => $this->bigInteger()->unsigned(),
        ]);

        $this->addForeignKey(
            'SPECIALIST2VACCINATION_STATION_SPECIALIST_ID',
            'public.specialist2vaccination_station',
            'specialist_id',
            'public.specialists',
            'id');
        $this->addForeignKey(
            'SPECIALIST2VACCINATION_STATION_ORGANIZATION_ID',
            'public.specialist2vaccination_station',
            'organization_id',
            'public.organizations',
            'id');
        $this->addForeignKey(
            'SPECIALIST2VACCINATION_STATION_VACCINATION_STATION_ID',
            'public.specialist2vaccination_station',
            'vaccination_station_id',
            'public.vaccination_stations',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.specialist2vaccination_station');
    }
}
