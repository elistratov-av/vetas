<?php

use app\commands\migrate\Migration;

/**
 * Class m180828_125220_refactor_diseases_table_flag_danger
 */
class m180828_125220_refactor_diseases_table_flag_danger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('diseases', ['flag_danger' => false], ['flag_danger' => null]);
        $this->execute('alter table "diseases" alter column flag_danger set NOT NULL;');
        $this->execute('alter table "diseases" alter column flag_danger set DEFAULT false;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('diseases', 'flag_danger', $this->boolean());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180828_125220_refactor_diseases_table_flag_danger cannot be reverted.\n";

        return false;
    }
    */
}
