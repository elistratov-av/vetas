<?php

use app\commands\migrate\Migration;

/**
 * Class m230311_132900_visits_table_add_call_reason
 */
class m230311_132900_visits_table_add_call_reason extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visits', 'call_reason', $this->string()->defaultValue(null));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visits', 'call_reason');

        return true;
    }
}
