<?php

use app\commands\migrate\Migration;

/**
 * Class m210517_114503_change_reason_vc_area_district_in_vaccination_station_table
 */
class m210517_114503_change_reason_vc_area_district_in_vaccination_station_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('public.vaccination_stations');
        $this->dropForeignKey('VACCINATION_STATIONS_AREA_ID', 'public.vaccination_stations');
        $this->dropForeignKey('VACCINATION_STATIONS_DISTRICT_ID', 'public.vaccination_stations');
        $this->dropForeignKey('VACCINATION_STATIONS_REASON_VC_ID', 'public.vaccination_stations');
        $this->dropColumn('public.vaccination_stations', 'area_id');
        $this->dropColumn('public.vaccination_stations', 'district_id');
        $this->dropColumn('public.vaccination_stations', 'reason_vc_id');

        $this->addColumn('public.vaccination_stations', 'area_id', $this->string()->notNull());
        $this->addColumn('public.vaccination_stations', 'district_id', $this->string()->notNull());
        $this->addColumn(
            'public.vaccination_stations',
            'reason_vc_id',
            $this->bigInteger()->unsigned()->null()
        );

        $this->addForeignKey(
            'VACCINATION_STATIONS_REASON_VC_ID',
            'public.vaccination_stations',
            'reason_vc_id',
            'public.reason_vc',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('public.vaccination_stations');
        $this->dropForeignKey('VACCINATION_STATIONS_REASON_VC_ID', 'public.vaccination_stations');
        $this->dropColumn('public.vaccination_stations', 'area_id');
        $this->dropColumn('public.vaccination_stations', 'district_id');
        $this->dropColumn('public.vaccination_stations', 'reason_vc_id');

        $this->addColumn(
            'public.vaccination_stations',
            'area_id',
            $this->bigInteger()->unsigned()->notNull()
        );
        $this->addColumn(
            'public.vaccination_stations',
            'district_id',
            $this->bigInteger()->unsigned()->notNull()
        );
        $this->addColumn(
            'public.vaccination_stations',
            'reason_vc_id',
            $this->bigInteger()->unsigned()->notNull()
        );

        $this->addForeignKey(
            'VACCINATION_STATIONS_AREA_ID',
            'public.vaccination_stations',
            'area_id',
            'public.areas',
            'id');
        $this->addForeignKey(
            'VACCINATION_STATIONS_DISTRICT_ID',
            'public.vaccination_stations',
            'district_id',
            'public.districts',
            'id');
        $this->addForeignKey(
            'VACCINATION_STATIONS_REASON_VC_ID',
            'public.vaccination_stations',
            'reason_vc_id',
            'public.reason_vc',
            'id');
    }
}
