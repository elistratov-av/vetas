<?php

use app\commands\migrate\Migration;

/**
 * Class m231018_050858_gost_diseases_dictionary_constraints_fix
 */
class m231018_050858_gost_diseases_dictionary_constraints_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%gost_diseases}}', 'name', $this->string(250));
        $this->alterColumn('{{%gost_diseases}}', 'gost_code', $this->string(20));
        $this->alterColumn('{{%gost_disease_sub_categories}}', 'name', $this->string(250));
        $this->alterColumn('{{%gost_disease_categories}}', 'name', $this->string(250));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%gost_diseases}}', 'name', $this->string(512));
        $this->alterColumn('{{%gost_diseases}}', 'gost_code', $this->string(255));
        $this->alterColumn('{{%gost_disease_sub_categories}}', 'name', $this->string(512));
        $this->alterColumn('{{%gost_disease_categories}}', 'name', $this->string(512));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231018_050858_gost_diseases_dictionary_constraints_fix cannot be reverted.\n";

        return false;
    }
    */
}
