<?php

use app\commands\migrate\Migration;

/**
 * Class m190912_092203_change_odopm_attributes_specification_fields_name
 */
class m190912_092203_change_odopm_attributes_specification_fields_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_type_id to type_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_name to name");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_type to type");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_primary to is_primary");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_edit to is_edit");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_required to is_required");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_field_mask to field_mask");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_tech_name to tech_name");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_max_length to max_length");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_max_length_deci to max_length_decimal");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_dictionary_id to dictionary_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_ref_catalog_id to ref_catalog_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_deleted to is_deleted");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_tmp_deleted to is_tmp_deleted");
        $this->execute("alter table odopm.odopm_attributes_specification rename column attribute_is_multi to is_multi");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("alter table odopm.odopm_attributes_specification rename column type_id to attribute_type_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column name to attribute_name");
        $this->execute("alter table odopm.odopm_attributes_specification rename column type to attribute_type");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_primary to attribute_is_primary");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_edit to attribute_is_edit");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_required to attribute_is_required");
        $this->execute("alter table odopm.odopm_attributes_specification rename column field_mask to attribute_field_mask");
        $this->execute("alter table odopm.odopm_attributes_specification rename column tech_name to attribute_tech_name");
        $this->execute("alter table odopm.odopm_attributes_specification rename column max_length to attribute_max_length");
        $this->execute("alter table odopm.odopm_attributes_specification rename column max_length_decimal to attribute_max_length_deci");
        $this->execute("alter table odopm.odopm_attributes_specification rename column dictionary_id to attribute_dictionary_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column ref_catalog_id to attribute_ref_catalog_id");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_deleted to attribute_is_deleted");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_tmp_deleted to attribute_is_tmp_deleted");
        $this->execute("alter table odopm.odopm_attributes_specification rename column is_multi to attribute_is_multi");
    }

}
