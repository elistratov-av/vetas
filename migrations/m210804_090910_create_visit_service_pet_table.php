<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_service_pet`.
 */
class m210804_090910_create_visit_service_pet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_service_pet', [
            'id_visits_gov_service' => $this->integer()->notNull(),
            'id_pet' => $this->integer()->notNull(),
            'PRIMARY KEY (id_visits_gov_service, id_pet)',
        ]);
        $this->addForeignKey(
            'fk-visit_service_pet-id_pet',
            'visit_service_pet',
            'id_pet',
            'pets',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-visit_service_pet-id_visits_gov_service',
            'visit_service_pet',
            'id_visits_gov_service',
            'visits_gov_services',
            'id',
            'CASCADE'
        );

        $this->addCommentOnTable(
            'visit_service_pet',
            'Связь n:m между <услуга-в-приёме> (visits_gov_services) и <животное> (pets)'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('visit_service_pet');
    }
}
