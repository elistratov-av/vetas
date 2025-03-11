<?php

use app\commands\migrate\Migration;

/**
 * Class m210825_140412_add_indexes_for_tables
 */
class m210825_140412_add_indexes_for_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-visits_gov_services-id_visit', 'visits_gov_services', 'id_visit');
        $this->createIndex('idx-visit_pets-id_visit', 'visit_pets', 'id_visit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-visits_gov_services-id_visit', 'visits_gov_services');
        $this->dropIndex('idx-visit_pets-id_visit', 'visit_pets');
    }
}
