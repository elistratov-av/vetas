<?php

use yii\db\Migration;

/**
 * Handles the creation of table `services_specialists`.
 */
class m190214_131017_create_services_specialists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('services_specialists', [
            'id' => $this->primaryKey(),
            'id_service' => $this->integer()->notNull(),
            'id_specialist' => $this->integer()->notNull(),
            'id_organization' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk-services_specialists-id_service',
            'services_specialists',
            'id_service',
            'gov_services',
            'id'
        );

        $this->addForeignKey(
            'fk-services_specialists-id_specialist',
            'services_specialists',
            'id_specialist',
            'specialists',
            'id'
        );

        $this->addForeignKey(
            'fk-services_specialists-id_organization',
            'services_specialists',
            'id_organization',
            'organizations',
            'id'
        );

        $this->createIndex(
            'idx-services_specialists-unique',
            'services_specialists',
            ['id_service', 'id_specialist', 'id_organization'],
            true
        );

        $this->createIndex(
            'idx-services_specialists-spec_org_services',
            'services_specialists',
            ['id_specialist', 'id_organization']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-services_specialists-unique', 'services_specialists');
        $this->dropIndex('idx-services_specialists-spec_org_services', 'services_specialists');
        $this->dropForeignKey('fk-services_specialists-id_organization', 'services_specialists');
        $this->dropForeignKey('fk-services_specialists-id_specialist', 'services_specialists');
        $this->dropForeignKey('fk-services_specialists-id_service', 'services_specialists');
        $this->dropTable('services_specialists');
    }
}
