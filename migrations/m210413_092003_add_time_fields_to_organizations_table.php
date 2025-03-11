<?php

use app\commands\migrate\Migration;

/**
 * Class m210413_092003_add_time_fields_to_organizations_table
 */
class m210413_092003_add_time_fields_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.organizations', 'time_from', $this->string());
        $this->addColumn('public.organizations', 'time_to', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.organizations', 'time_from');
        $this->dropColumn('public.organizations', 'time_to');
    }
}
