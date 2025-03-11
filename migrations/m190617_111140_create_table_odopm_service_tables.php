<?php

use app\commands\migrate\Migration;

/**
 * Class m190617_111140_create_table_odopm_service_tables
 */
class m190617_111140_create_table_odopm_service_tables extends Migration
{
    public function safeUp()
    {

        $this->createTable('odopm_catalogs', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(),
            'id_odopm' => $this->integer(),
            'name' => $this->string(255),
            'period' => $this->string(255),
            'updated_at' => $this->date(),
        ]);

        $this->createTable('odopm_catalogs_item', [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(),
            'id_catalog' => $this->integer(),
            'entity_type' => $this->string(50),
            'entity_id' => $this->integer(),
            'global_id' => $this->integer(),
        ]);


        $this->createTable('odopm_config', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(),
            'reseption_corpses' => $this->boolean(),
            'free_vaccination' => $this->boolean(),
            'pet_registration' => $this->boolean(),
        ]);


        $this->createTable('odopm_organizations', [
            'id' => $this->primaryKey(),
            'global_id' => $this->integer(),
            'system_object_id' => $this->integer(),
            'id_code' => $this->integer(),
            'full_name' => $this->string(2048),
            'short_name' => $this->string(2048),
            'inn' => $this->string(2048),
            'kpp' => $this->string(2048),
            'ogrn' => $this->string(2048),
            'is_capital_structure' => $this->boolean(),
            'bti_area_code' => $this->integer(),
            'bti_district_code' => $this->integer(),
            'address' => $this->string(2048),
            'phone' => $this->string(2048),
            'work_hours' => $this->string(2048),
            'clarification_work_hours' => $this->string(2048),
            'comments' => $this->string(2048),
            'signature' => $this->string(4000),
            'id_odopm_catalog' => $this->integer(),
        ]);

        $this->createTable('odopm_areas', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'bti_code' => $this->string()
        ]);

        $this->createTable('odopm_districts', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'bti_code' => $this->string(),
            'id_area' => $this->string(),
            'comment' => $this->text()
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm_catalogs');
        $this->dropTable('odopm_config');
        $this->dropTable('odopm_catalogs_item');
        $this->dropTable('odopm_organizations');
        $this->dropTable('odopm_areas');
        $this->dropTable('odopm_districts');
    }

}
