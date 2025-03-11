<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visits_junctions`.
 */
class m180706_124951_create_visits_junctions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visits_specialists', [
            'id_visit' => $this->integer()->notNull()->comment('Ссылка на запись приема'),
            'id_specialist' => $this->integer()->notNull()->comment('Ссылка на специалиста')
        ]);
        $this->addPrimaryKey('visits_specialists_pkey', 'visits_specialists', ['id_visit', 'id_specialist']);
        $this->addCommentOnTable('visits_specialists', 'Таблица связи записи на прием и специалистов');

        $this->addForeignKey(
            'fk-visits_specialists-id_visit',
            'visits_specialists',
            'id_visit',
            'visits',
            'id'
        );

        $this->addForeignKey(
            'fk-visits_specialists-id_specialist',
            'visits_specialists',
            'id_specialist',
            'specialists',
            'id'
        );

        $this->createTable('visits_gov_services', [
            'id_visit' => $this->integer()->notNull()->comment('Ссылка на запись приема'),
            'id_service' => $this->integer()->notNull()->comment('Ссылка на услугу'),
            'count' => $this->integer()
        ]);
        $this->addPrimaryKey('visits_gov_services_pkey', 'visits_gov_services', ['id_visit', 'id_service']);
        $this->addCommentOnTable('visits_gov_services', 'Таблица связи записи на прием и услуг');

        $this->addForeignKey(
            'fk-visits_gov_services-id_visit',
            'visits_gov_services',
            'id_visit',
            'visits',
            'id'
        );

        $this->addForeignKey(
            'fk-visits_gov_services-id_service',
            'visits_gov_services',
            'id_service',
            'gov_services',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-visits_specialists-id_visit', 'visits_specialists');
        $this->dropForeignKey('fk-visits_specialists-id_specialist', 'visits_specialists');
        $this->dropTable('visits_specialists');

        $this->dropForeignKey('fk-visits_gov_services-id_visit', 'visits_gov_services');
        $this->dropForeignKey('fk-visits_gov_services-id_service', 'visits_gov_services');
        $this->dropTable('visits_gov_services');
    }
}
