<?php

use app\commands\migrate\Migration;

/**
 * Class m180924_105520_update_balance_equipments_manufactured_number_drop_not_null
 */
class m180924_105520_update_balance_equipments_manufactured_number_drop_not_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE balance_equipments ALTER COLUMN manufactured_number DROP NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180924_105520_update_balance_equipments_manufactured_number_drop_not_null cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180924_105520_update_balance_equipments_manufactured_number_drop_not_null cannot be reverted.\n";

        return false;
    }
    */
}
