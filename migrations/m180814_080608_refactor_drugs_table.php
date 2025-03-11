<?php

use yii\db\Migration;

/**
 * Class m180814_080608_refactor_drugs_table
 */
class m180814_080608_refactor_drugs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('drugs', 'id_active_substance_measure');
        $this->dropColumn('drugs', 'id_file_packaging_image');
        $this->dropColumn('drugs', 'active_substance_unit');
        $this->dropColumn('drugs', 'id_active_substance');
        $this->dropColumn('drugs', 'packaging');
        $this->dropColumn('drugs', 'unit');

        $this->dropForeignKey('fk-drugs-id_manufactured', 'drugs');
        $this->dropForeignKey('fk-drugs-id_representation', 'drugs');

        $this->renameColumn('drugs', 'id_manufactured', 'id_produced');
        $this->renameColumn('drugs', 'id_representation', 'id_dealer');

        $this->addForeignKey('fk-drugs-id_produced', 'drugs', 'id_produced', 'drug_orgs', 'id');
        $this->addForeignKey('fk-drugs-id_dealer', 'drugs', 'id_dealer', 'drug_orgs', 'id');

        $this->alterColumn('drugs', 'basis', $this->text());
        $this->alterColumn('drugs', 'form', $this->text());
        $this->alterColumn('drugs', 'form_description', $this->text());
        $this->alterColumn('drugs', 'excipients', $this->text());

        $this->addColumn('drugs', 'unit', $this->double());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('drugs', 'id_active_substance_measure', $this->integer());
        $this->addColumn('drugs', 'id_file_packaging_image', $this->integer());
        $this->addColumn('drugs', 'active_substance_unit', $this->string());
        $this->addColumn('drugs', 'id_active_substance', $this->integer());
        $this->addColumn('drugs', 'packaging', $this->string());

        $this->alterColumn('drugs', 'basis', $this->string());
        $this->alterColumn('drugs', 'form', $this->string());
        $this->alterColumn('drugs', 'form_description', $this->string());
        $this->alterColumn('drugs', 'excipients', $this->string());
        $this->alterColumn('drugs', 'unit', $this->string());

        $this->dropForeignKey('fk-drugs-id_produced', 'drugs');
        $this->dropForeignKey('fk-drugs-id_dealer', 'drugs');

        $this->renameColumn('drugs', 'id_produced', 'id_manufactured');
        $this->renameColumn('drugs', 'id_dealer', 'id_representation');

        $this->addForeignKey('fk-drugs-id_manufactured', 'drugs', 'id_manufactured', 'drug_orgs', 'id');
        $this->addForeignKey('fk-drugs-id_representation', 'drugs', 'id_representation', 'drug_orgs', 'id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180814_080608_refactor_drugs_table cannot be reverted.\n";

        return false;
    }
    */
}
