<?php

use app\commands\migrate\Migration;

/**
 * Class m190820_094156_create_table_odopm_vet_organizations
 */
class m190820_094156_create_table_odopm_vet_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('odopm.odopm_vet_organizations', [
            'id' => $this->primaryKey(),
            'global_id' => $this->integer(),
            'system_object_id' => $this->integer(),
            'id_code' => $this->integer(5),
            'full_name' => $this->string(),
            'short_name' => $this->string(100),
            'chief_name' => $this->string(100),
            'chief_position' => $this->string(100),
            'responsible_department_id' => $this->integer()->comment('id ответственной организации'),
            'public_service_availible' => $this->boolean()->comment('флаг оказываются ли услуги населению'),
            'inn' => $this->string(),
            'kpp' => $this->string(),
            'ogrn' => $this->string(),
            'id_area' => $this->integer(),
            'id_district' => $this->integer(),
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
            'signature' => $this->boolean(),
            'geodata' => $this->string(3000)

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm.odopm_vet_organizations');
    }
}
