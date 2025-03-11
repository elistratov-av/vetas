<?php

use yii\db\Migration;

/**
 * Class m180712_103611_shift_colour
 */
class m180712_103611_shift_colour extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('shifts', 'colour', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180712_103611_shift_colour cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180712_103611_shift_colour cannot be reverted.\n";

        return false;
    }
    */
}
