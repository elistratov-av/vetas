<?php

use app\commands\migrate\Migration;

/**
 * Class m210417_092001_create_vaccination_stations_table
 */
class m210417_092001_create_vaccination_stations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.vaccination_stations', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(),
            'short_name' => $this->string(),
            'parent_id' => $this->bigInteger()->unsigned()->notNull(),
            'number' => $this->string()->notNull(),
            'kind_vc_id' => $this->bigInteger()->unsigned()->notNull(),
            'date' => $this->date()->notNull(),
            'area_id' => $this->bigInteger()->unsigned()->notNull(),
            'district_id' => $this->bigInteger()->unsigned()->notNull(),
            'reason_vc_id' => $this->bigInteger()->unsigned()->notNull(),
            'fias_address_id' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'VACCINATION_STATIONS_PARENT_ID',
            'public.vaccination_stations',
            'parent_id',
            'public.organizations',
            'id');
        $this->addForeignKey(
            'VACCINATION_STATIONS_KIND_VC_ID',
            'public.vaccination_stations',
            'kind_vc_id',
            'public.kind_vc',
            'id');
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
        $this->addForeignKey(
            'VACCINATION_STATIONS_FIAS_ADDRESS_ID',
            'public.vaccination_stations',
            'fias_address_id',
            'public.fias_addresses',
            'id');
        $this->addForeignKey(
            'VACCINATION_STATIONS_CREATED_BY',
            'public.vaccination_stations',
            'created_by',
            'public.users',
            'id');
        $this->addForeignKey(
            'VACCINATION_STATIONS_UPDATED_BY',
            'public.vaccination_stations',
            'updated_by',
            'public.users',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.vaccination_stations');
    }
}
