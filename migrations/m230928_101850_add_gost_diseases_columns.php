<?php

use app\commands\migrate\Migration;

/**
 * Class m230928_101850_add_gost_diseases_columns
 */
class m230928_101850_add_gost_diseases_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gost_diseases', 'created_by', $this->integer());
        $this->addColumn('gost_diseases', 'updated_by', $this->integer());
        $this->addColumn('gost_diseases', 'created_at', $this->dateTime());
        $this->addColumn('gost_diseases', 'updated_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gost_diseases', 'created_by');
        $this->dropColumn('gost_diseases', 'updated_by');
        $this->dropColumn('gost_diseases', 'created_at');
        $this->dropColumn('gost_diseases', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230928_101850_add_gost_diseases_columns cannot be reverted.\n";

        return false;
    }
    */
}
