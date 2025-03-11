<?php

use app\commands\migrate\Migration;

/**
 * Class m210412_140045_add_id_fact_fias_address_to_organizations_table
 */
class m210412_140045_add_id_fact_fias_address_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.organizations', 'id_fact_fias_address', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.organizations', 'id_fact_fias_address');
    }
}
