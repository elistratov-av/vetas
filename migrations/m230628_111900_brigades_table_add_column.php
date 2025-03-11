<?php

use app\commands\migrate\Migration;

/**
 * Class m230628_111900_brigades_table_add_column
 */
class m230628_111900_brigades_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE public.brigades ADD COLUMN coordinates point default null; ");
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.brigades', 'coordinates');

        return true;
    }
}
