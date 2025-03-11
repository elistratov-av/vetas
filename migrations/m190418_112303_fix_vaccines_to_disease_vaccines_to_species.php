<?php

use app\commands\migrate\Migration;

/**
 * Class m190418_112303_fix_vaccines_to_disease_vaccines_to_species
 */
class m190418_112303_fix_vaccines_to_disease_vaccines_to_species extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('vaccines_to_diseases', 'created_by', $this->integer());
        $this->addColumn('vaccines_to_diseases', 'updated_by', $this->integer());
        $this->addColumn('vaccines_to_diseases', 'created_at', $this->timestamp(0));
        $this->addColumn('vaccines_to_diseases', 'updated_at', $this->timestamp(0));

        $this->addColumn('vaccines_to_species', 'created_by', $this->integer());
        $this->addColumn('vaccines_to_species', 'updated_by', $this->integer());
        $this->addColumn('vaccines_to_species', 'created_at', $this->timestamp(0));
        $this->addColumn('vaccines_to_species', 'updated_at', $this->timestamp(0));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('vaccines_to_diseases', 'created_by');
        $this->dropColumn('vaccines_to_diseases', 'updated_by');
        $this->dropColumn('vaccines_to_diseases', 'created_at');
        $this->dropColumn('vaccines_to_diseases', 'updated_at');

        $this->dropColumn('vaccines_to_species', 'created_by');
        $this->dropColumn('vaccines_to_species', 'updated_by');
        $this->dropColumn('vaccines_to_species', 'created_at');
        $this->dropColumn('vaccines_to_species', 'updated_at');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190418_112303_fix_vaccines_to_disease_vaccines_to_species cannot be reverted.\n";

        return false;
    }
    */
}
