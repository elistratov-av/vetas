<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pet_owners}}`.
 */
class m230614_165236_add_columns_to_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'is_veteran_infosoc', $this->boolean()->defaultValue(false));
        $this->addColumn('pet_owners', 'is_disabled_infosoc', $this->boolean()->defaultValue(false));
        $this->addColumn('pet_owners', 'is_family_disabled_children_infosoc', $this->boolean()->defaultValue(false));
        $this->addColumn('pet_owners', 'is_blind_infosoc', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('pet_owners', 'is_veteran_infosoc');
        $this->addColumn('pet_owners', 'is_disabled_infosoc');
        $this->addColumn('pet_owners', 'is_family_disabled_children_infosoc');
        $this->addColumn('pet_owners', 'is_blind_infosoc');
    }
}
