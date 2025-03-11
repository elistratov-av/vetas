<?php

use app\commands\migrate\Migration;

/**
 * Class m210526_135503_add_reg_number_to_pets_table
 */
class m210526_135503_add_reg_number_to_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pets', 'reg_number', $this->string());
        $this->addColumn('public.pets', 'size_id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pets', 'reg_number');
        $this->dropColumn('public.pets', 'size_id');
    }
}
