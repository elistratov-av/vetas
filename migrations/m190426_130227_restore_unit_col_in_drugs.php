<?php

use app\commands\migrate\Migration;

/**
 * Class m190426_130227_restore_unit_col_in_drugs
 */
class m190426_130227_restore_unit_col_in_drugs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'drugs',
            'unit',
            $this->double()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'drugs',
            'unit'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190426_130227_restore_unit_col_in_drugs cannot be reverted.\n";

        return false;
    }
    */
}
