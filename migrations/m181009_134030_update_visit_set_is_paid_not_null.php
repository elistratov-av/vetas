<?php

use app\commands\migrate\Migration;

/**
 * Class m181009_134030_update_visit_set_is_paid_not_null
 */
class m181009_134030_update_visit_set_is_paid_not_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('UPDATE public.visits SET is_paid = false WHERE is_paid IS NULL');
        $this->execute('ALTER TABLE public.visits ALTER COLUMN is_paid SET NOT NULL');
        $this->execute('ALTER TABLE public.visits ALTER COLUMN is_paid SET DEFAULT false');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181009_134030_update_visit_set_is_paid_not_null cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_134030_update_visit_set_is_paid_not_null cannot be reverted.\n";

        return false;
    }
    */
}
