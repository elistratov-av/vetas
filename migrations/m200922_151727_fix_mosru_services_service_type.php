<?php

use app\commands\migrate\Migration;

/**
 * Class m200922_151727_fix_mosru_services_service_type
 */
class m200922_151727_fix_mosru_services_service_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('public.gov_services', ['id_service_type' => 5], ['id' => 269]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('public.gov_services', ['id_service_type' => 15], ['id' => 269]);
    }
}
