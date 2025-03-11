<?php

use app\commands\migrate\Migration;

/**
 * Class m230630_124900_pets_table_add_column
 */
class m230630_124900_pets_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'representatives', $this->json()->defaultValue(null)->comment('Представители животного'));
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pets', 'representatives');

        return true;
    }
}
