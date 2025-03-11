<?php

use app\commands\migrate\Migration;

/**
 * Class m210416_094503_drop_time_fields_in_organizations_table
 */
class m210416_094503_drop_time_fields_in_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('public.organizations', 'time_from');
        $this->dropColumn('public.organizations', 'time_to');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('public.organizations', 'time_from', $this->string());
        $this->addColumn('public.organizations', 'time_to', $this->string());
    }
}
