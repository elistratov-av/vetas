<?php

use app\commands\migrate\Migration;

/**
 * Class m230308_121900_visits_table_add_column
 */
class m230308_121900_visits_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visits', 'id_request', $this->integer());
        $this->addColumn('public.visits', 'id_brigade', $this->integer());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visits', 'id_request');
        $this->dropColumn('public.visits', 'id_brigade');

        return true;
    }
}
