<?php

use app\commands\migrate\Migration;

/**
 * Class m190212_142812_fix_created_at_updated_at
 */
class m190212_142812_fix_created_at_updated_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('public.balance_exp_materials', 'created_at', $this->timestamp(0));
        $this->alterColumn('public.balance_exp_materials', 'updated_at', $this->timestamp(0));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190212_142812_fix_created_at_updated_at cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190212_142812_fix_created_at_updated_at cannot be reverted.\n";

        return false;
    }
    */
}
