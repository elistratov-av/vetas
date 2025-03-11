<?php

use app\commands\migrate\Migration;

/**
 * Class m230710_092400_pets_table_add_column
 */
class m230710_092400_pets_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'mosru_guide_dog', $this->boolean()->defaultValue(null)->comment('Повадырь с мдм'));
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pets', 'mosru_guide_dog');

        return true;
    }
}
