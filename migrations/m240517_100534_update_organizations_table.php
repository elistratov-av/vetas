<?php

use app\commands\migrate\Migration;

/**
 * Class m240517_100534_update_organizations_table
 */
class m240517_100534_update_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.organizations', 'hidden', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.organizations', 'hidden');
    }
}
