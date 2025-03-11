<?php

use yii\db\Migration;

/**
 * Handles the creation of table `service_tmcs`.
 * Has foreign keys to the tables:
 *
 * - `gov_services`
 */
class m180725_095035_create_service_tmcs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('service_tmcs', [
            'id' => $this->primaryKey(),
            'id_tmc' => $this->integer()->defaultValue(null),
            'tmc_class' => $this->string(),
            'is_required' => $this->boolean()->notNull(),
            'id_service' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_service`
        $this->createIndex(
            'idx-service_tmcs-id_service',
            'service_tmcs',
            'id_service'
        );

        // add foreign key for table `gov_services`
        $this->addForeignKey(
            'fk-service_tmcs-id_service',
            'service_tmcs',
            'id_service',
            'gov_services',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-service_tmcs-unique_tmc_service',
            'service_tmcs',
            ['id_tmc', 'id_service'],
            true
        );

        $this->createIndex(
            'idx-service_tmcs-unique_tmc_class',
            'service_tmcs',
            ['id_service', 'tmc_class'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `gov_services`
        $this->dropForeignKey(
            'fk-service_tmcs-id_service',
            'service_tmcs'
        );

        // drops index for column `id_service`
        $this->dropIndex(
            'idx-service_tmcs-id_service',
            'service_tmcs'
        );

        $this->dropIndex(
            'idx-service_tmcs-unique_tmc_service',
            'service_tmcs'
        );

        $this->dropIndex(
            'idx-service_tmcs-unique_tmc_class',
            'service_tmcs'
        );

        $this->dropTable('service_tmcs');
    }
}
