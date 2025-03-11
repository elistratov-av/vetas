<?php

use app\commands\migrate\Migration;

/**
 * Class m230306_140600_brigades_table_add_column
 */
class m230306_140600_brigades_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.brigades', 'car_driver', $this->string(255));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.brigades', 'car_driver');

        return true;
    }
}
