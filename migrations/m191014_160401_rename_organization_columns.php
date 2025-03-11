<?php

use app\commands\migrate\Migration;

/**
 * Class m191014_160401_rename_organization_columns
 */
class m191014_160401_rename_organization_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('organizations', 'public_service_available', 'public_services_available');
        $this->renameColumn('organizations', 'point_vaccination', 'free_vaccination');
        $this->renameColumn('organizations', 'point_utilization', 'reseption_corpses');
        $this->renameColumn('organizations', 'point_registration', 'pet_registration');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('organizations', 'public_services_available', 'public_service_available');
        $this->renameColumn('organizations', 'free_vaccination', 'point_vaccination');
        $this->renameColumn('organizations', 'reseption_corpses', 'point_utilization');
        $this->renameColumn('organizations', 'pet_registration', 'point_registration');
    }
}
