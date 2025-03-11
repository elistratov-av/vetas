<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_service_tmcs`.
 * Has foreign keys to the tables:
 *
 * - `gov_services`
 * - `visits`
 */
class m180725_094627_create_visit_service_tmcs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_service_tmcs', [
            'id' => $this->primaryKey(),
            'count' => $this->integer()->notNull(),
            'id_tmc' => $this->integer()->notNull(),
            'id_service' => $this->integer()->notNull(),
            'id_visit' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_service`
        $this->createIndex(
            'idx-visit_service_tmcs-id_service',
            'visit_service_tmcs',
            'id_service'
        );

        // add foreign key for table `gov_services`
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_service',
            'visit_service_tmcs',
            'id_service',
            'gov_services',
            'id',
            'CASCADE'
        );

        // creates index for column `id_visit`
        $this->createIndex(
            'idx-visit_service_tmcs-id_visit',
            'visit_service_tmcs',
            'id_visit'
        );

        // add foreign key for table `visits`
        $this->addForeignKey(
            'fk-visit_service_tmcs-id_visit',
            'visit_service_tmcs',
            'id_visit',
            'visits',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `gov_services`
        $this->dropForeignKey(
            'fk-visit_service_tmcs-id_service',
            'visit_service_tmcs'
        );

        // drops index for column `id_service`
        $this->dropIndex(
            'idx-visit_service_tmcs-id_service',
            'visit_service_tmcs'
        );

        // drops foreign key for table `visits`
        $this->dropForeignKey(
            'fk-visit_service_tmcs-id_visit',
            'visit_service_tmcs'
        );

        // drops index for column `id_visit`
        $this->dropIndex(
            'idx-visit_service_tmcs-id_visit',
            'visit_service_tmcs'
        );

        $this->dropTable('visit_service_tmcs');
    }
}
