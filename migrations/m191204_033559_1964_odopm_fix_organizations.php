<?php

use app\commands\migrate\Migration;

/**
 * Class m191204_033559_1964_odopm_fix_organizations
 */
class m191204_033559_1964_odopm_fix_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('odopm.odopm_config');
        $this->dropTable('odopm.odopm_organizations');
        $this->dropTable('odopm.odopm_vet_organizations');

        $this->createTable('odopm.odopm_organizations', [
            'id' => $this->primaryKey(),
            'global_id' => $this->integer(),
            'system_object_id' => $this->integer(),
            'id_code' => $this->integer(5),
            'full_name' => $this->string(),
            'short_name' => $this->string(100),
            'chief_name' => $this->string(100),
            'chief_position' => $this->string(100),
            'responsible_department_id' => $this->integer()->comment('id ответственной организации'),
            'public_services_available' => $this->boolean()->comment('флаг оказываются ли услуги населению'),
            'inn' => $this->string(),
            'kpp' => $this->string(),
            'ogrn' => $this->string(),
            'bti_area_code' => $this->string(),
            'bti_district_code' => $this->string(),
            'address' => $this->string(),
            'unom' => $this->string(),
            'public_phone' => $this->string(),
            'working_hours' => $this->string(),
            'clarification_work_hours' => $this->string(1000),
            'comments' => $this->string(3000),
            'entry_state_id' => $this->integer(),
            'entry_add_reason_id' => $this->integer(),
            'entry_change_reason_id' => $this->integer(),
            'entry_delete_reason_id' => $this->integer(),
            'parent_entries' => $this->string(),
            'child_entries' => $this->string(),
            'geodata' => $this->string(3000),
            'is_capital_structure' => $this->boolean(),
            'signature' => $this->boolean(),
            'id_odopm_catalog' => $this->integer(),
            'reseption_corpses' => $this->boolean()->defaultValue(false),
            'free_vaccination' => $this->boolean()->defaultValue(false),
            'pet_registration' => $this->boolean()->defaultValue(false),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm.odopm_organizations');
    }
}
