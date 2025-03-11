<?php

use app\commands\migrate\Migration;

/**
 * Class m210412_152055_drop_id_fact_fias_address_in_organizations_table
 */
class m210412_152055_drop_id_fact_fias_address_in_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('public.organizations', 'id_fact_fias_address');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('public.organizations', 'id_fact_fias_address', $this->integer());
    }
}
