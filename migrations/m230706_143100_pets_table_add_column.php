<?php

use app\commands\migrate\Migration;

/**
 * Class m230706_143100_pets_table_add_column
 */
class m230706_143100_pets_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'mosru_delete_reason', $this->integer()->defaultValue(null)->comment('Причина удаления'));
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pets', 'mosru_delete_reason');

        return true;
    }
}
