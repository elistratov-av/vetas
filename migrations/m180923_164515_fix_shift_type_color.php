<?php

use app\commands\migrate\Migration;

/**
 * Class m180923_164515_fix_shift_type_color
 */
class m180923_164515_fix_shift_type_color extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('shift_type',[
            'colour' => '#dadee6',
        ],[
            'type' => 'HOLIDAY'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('shift_type',[
            'colour' => 'dadee6',
        ],[
            'type' => 'HOLIDAY'
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180923_164515_fix_shift_type_color cannot be reverted.\n";

        return false;
    }
    */
}
