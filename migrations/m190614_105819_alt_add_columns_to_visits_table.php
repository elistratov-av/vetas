<?php

use app\commands\migrate\Migration;

/**
 * Class m190614_105819_alt_add_columns_to_visits_table
 */
class m190614_105819_alt_add_columns_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'time_signed', $this->date());
        $this->addColumn('visits', 'is_signed', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'time_signed');
        $this->dropColumn('visits', 'is_signed');
    }
}
