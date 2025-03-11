<?php

use yii\db\Migration;

/**
 * Handles the creation of table `id_service_index_in_visits_gov_services`.
 */
class m190326_064214_create_id_service_index_in_visits_gov_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-visits_gov_services-id_service',
            'visits_gov_services',
            'id_service'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-visits_gov_services-id_service', 'visits_gov_services');
    }
}
