<?php

use app\commands\migrate\Migration;

/**
 * Class m180921_084007_rename_shift_type_color_col
 */
class m180921_084007_rename_shift_type_color_col extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('public.shift_type','color', 'colour');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('public.shift_type','colour', 'color');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180921_084007_rename_shift_type_color_col cannot be reverted.\n";

        return false;
    }
    */
}
