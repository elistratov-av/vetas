<?php

use yii\db\Migration;

/**
 * Handles the creation of table `odopm_attributes_specification`.
 */
class m190624_080610_create_odopm_attributes_specification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('odopm_attributes_specification', [
            'id' => $this->primaryKey(),
            'id_catalog' => $this->integer(),
            'attribute_id' => $this->integer(),
            'attribute_type_id' => $this->integer(),
            'attribute_name' => $this->string(32),
            'attribute_type' => $this->string(32),
            'attribute_is_primary' => $this->boolean(),
            'attribute_is_edit' => $this->boolean(),
            'attribute_is_required' => $this->boolean(),
            'attribute_field_mask' => $this->string(),
            'attribute_tech_name' => $this->string(32),
            'attribute_max_length' => $this->integer(),
            'attribute_max_length_deci' => $this->string(),
            'attribute_dictionary_id' => $this->integer(),
            'attribute_ref_catalog_id' => $this->integer(),
            'attribute_is_deleted' => $this->boolean(),
            'attribute_is_tmp_deleted' => $this->boolean(),
            'attribute_is_multi' => $this->boolean()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm_attributes_specification');
    }
}
