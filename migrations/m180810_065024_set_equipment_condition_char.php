<?php

use yii\db\Migration;

/**
 * Class m180810_065024_set_equipment_condition_char
 */
class m180810_065024_set_equipment_condition_char extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "balance_equipments" ALTER COLUMN "equipment_condition" TYPE char(1) USING "equipment_condition"::char');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180810_065024_set_equipment_condition_char cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180810_065024_set_equipment_condition_char cannot be reverted.\n";

        return false;
    }
    */
}
