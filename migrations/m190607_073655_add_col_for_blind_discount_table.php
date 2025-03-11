<?php

use app\commands\migrate\Migration;

/**
 * Class m190607_073655_add_col_for_blind_discount_table
 */
class m190607_073655_add_col_for_blind_discount_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.discount', 'is_for_blind', 'bool');
        $this->update('public.discount', ['is_for_blind' => false]);
        $this->addCommentOnColumn('public.discount', 'is_for_blind', 'true, если льгота предоставляется незрячим людям');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.discount', 'is_for_blind');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190607_073655_add_col_for_blind_discount_table cannot be reverted.\n";

        return false;
    }
    */
}
