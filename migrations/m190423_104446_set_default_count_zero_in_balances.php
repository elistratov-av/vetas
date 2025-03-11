<?php

use app\commands\migrate\Migration;

/**
 * Class m190423_104446_set_default_count_zero_in_balances
 */
class m190423_104446_set_default_count_zero_in_balances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.balance_drugs ALTER COLUMN dose_count SET DEFAULT 0;');
        $this->execute('ALTER TABLE public.balance_exp_materials ALTER COLUMN count SET DEFAULT 0;');
        $this->execute('ALTER TABLE public.balance_vaccines ALTER COLUMN dose_count SET DEFAULT 0;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE public.balance_drugs ALTER COLUMN dose_count DROP DEFAULT;');
        $this->execute('ALTER TABLE public.balance_exp_materials ALTER COLUMN count DROP DEFAULT;');
        $this->execute('ALTER TABLE public.balance_vaccines ALTER COLUMN dose_count DROP DEFAULT;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190423_104446_set_default_count_zero_in_balances cannot be reverted.\n";

        return false;
    }
    */
}
