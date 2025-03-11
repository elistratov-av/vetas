<?php

use app\commands\migrate\Migration;

/**
 * Class m231011_081005_add_gost_categories_columns
 */
class m231011_081005_add_gost_categories_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gost_disease_categories', 'created_by', $this->integer());
        $this->addColumn('gost_disease_categories', 'updated_by', $this->integer());
        $this->addColumn('gost_disease_categories', 'created_at', $this->dateTime());
        $this->addColumn('gost_disease_categories', 'updated_at', $this->dateTime());
        $this->addColumn('gost_disease_sub_categories', 'created_by', $this->integer());
        $this->addColumn('gost_disease_sub_categories', 'updated_by', $this->integer());
        $this->addColumn('gost_disease_sub_categories', 'created_at', $this->dateTime());
        $this->addColumn('gost_disease_sub_categories', 'updated_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gost_disease_categories', 'created_by');
        $this->dropColumn('gost_disease_categories', 'updated_by');
        $this->dropColumn('gost_disease_categories', 'created_at');
        $this->dropColumn('gost_disease_categories', 'updated_at');
        $this->dropColumn('gost_disease_sub_categories', 'created_by');
        $this->dropColumn('gost_disease_sub_categories', 'updated_by');
        $this->dropColumn('gost_disease_sub_categories', 'created_at');
        $this->dropColumn('gost_disease_sub_categories', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231011_081005_add_gost_categories_columns cannot be reverted.\n";

        return false;
    }
    */
}
