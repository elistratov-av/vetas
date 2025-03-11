<?php

use app\commands\migrate\Migration;

/**
 * Class m200817_012617_update_gov_services_add_indices
 */
class m200817_012617_update_gov_services_add_indices extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('create index if not exists "idx_gov_services_for_broods" on public.gov_services (for_broods)');
        $this->execute('create index if not exists "idx_gov_services_for_multiple" on public.gov_services (for_multiple)');
        $this->execute('create index if not exists "idx_gov_services_once_per_day" on public.gov_services (once_per_day)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('drop index if exists "idx_gov_services_for_broods"');
        $this->execute('drop index if exists "idx_gov_services_for_multiple"');
        $this->execute('drop index if exists "idx_gov_services_once_per_day"');
    }
}
