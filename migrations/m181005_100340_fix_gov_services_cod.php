<?php

use app\commands\migrate\Migration;

/**
 * Class m181005_100340_fix_gov_services_cod
 */
class m181005_100340_fix_gov_services_cod extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("alter table public.gov_services alter column cod type char(4) using cod::integer::text");
        $this->execute("update public.gov_services set cod = concat('0', cod)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
