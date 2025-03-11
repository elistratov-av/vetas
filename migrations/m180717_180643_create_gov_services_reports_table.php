<?php

use yii\db\Migration;

/**
 * Handles the creation of table `gov_services_reports`.
 * Has foreign keys to the tables:
 *
 * - `reports`
 * - `gov_services`
 */
class m180717_180643_create_gov_services_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('gov_services_reports', [
            'id' => $this->primaryKey(),
            'id_report' => $this->integer()->notNull(),
            'id_service' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_report`
        $this->createIndex(
            'idx-gov_services_reports-id_report',
            'gov_services_reports',
            'id_report'
        );

        // add foreign key for table `reports`
        $this->addForeignKey(
            'fk-gov_services_reports-id_report',
            'gov_services_reports',
            'id_report',
            'reports',
            'id',
            'CASCADE'
        );

        // creates index for column `id_service`
        $this->createIndex(
            'idx-gov_services_reports-id_service',
            'gov_services_reports',
            'id_service'
        );

        // add foreign key for table `gov_services`
        $this->addForeignKey(
            'fk-gov_services_reports-id_service',
            'gov_services_reports',
            'id_service',
            'gov_services',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `reports`
        $this->dropForeignKey(
            'fk-gov_services_reports-id_report',
            'gov_services_reports'
        );

        // drops index for column `id_report`
        $this->dropIndex(
            'idx-gov_services_reports-id_report',
            'gov_services_reports'
        );

        // drops foreign key for table `gov_services`
        $this->dropForeignKey(
            'fk-gov_services_reports-id_service',
            'gov_services_reports'
        );

        // drops index for column `id_service`
        $this->dropIndex(
            'idx-gov_services_reports-id_service',
            'gov_services_reports'
        );

        $this->dropTable('gov_services_reports');
    }
}
