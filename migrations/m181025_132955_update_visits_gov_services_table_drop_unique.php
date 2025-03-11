<?php

use app\commands\migrate\Migration;

/**
 * Class m181025_132955_update_visits_gov_services_table_drop_unique
 */
class m181025_132955_update_visits_gov_services_table_drop_unique extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('visits_gov_services_unique', 'visits_gov_services');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
