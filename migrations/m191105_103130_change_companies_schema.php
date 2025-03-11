<?php

use app\commands\migrate\Migration;

/**
 * Class m191105_103130_change_companies_schema
 */
class m191105_103130_change_companies_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE companies 
                            SET SCHEMA animalid;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191105_103130_change_companies_schema cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191105_103130_change_companies_schema cannot be reverted.\n";

        return false;
    }
    */
}
