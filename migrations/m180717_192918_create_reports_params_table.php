<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reports_params`.
 * Has foreign keys to the tables:
 *
 * - `gov_services`
 * - `reports`
 */
class m180717_192918_create_reports_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reports_params', [
            'id' => $this->primaryKey(),
            'id_param' => $this->integer()->notNull(),
            'id_report' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_param`
        $this->createIndex(
            'idx-reports_params-id_param',
            'reports_params',
            'id_param'
        );

        // add foreign key for table `params`
        $this->addForeignKey(
            'fk-reports_params-id_param',
            'reports_params',
            'id_param',
            'params',
            'id',
            'CASCADE'
        );

        // creates index for column `id_report`
        $this->createIndex(
            'idx-reports_params-id_report',
            'reports_params',
            'id_report'
        );

        // add foreign key for table `reports`
        $this->addForeignKey(
            'fk-reports_params-id_report',
            'reports_params',
            'id_report',
            'reports',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `params`
        $this->dropForeignKey(
            'fk-reports_params-id_param',
            'reports_params'
        );

        // drops index for column `id_param`
        $this->dropIndex(
            'idx-reports_params-id_param',
            'reports_params'
        );

        // drops foreign key for table `reports`
        $this->dropForeignKey(
            'fk-reports_params-id_report',
            'reports_params'
        );

        // drops index for column `id_report`
        $this->dropIndex(
            'idx-reports_params-id_report',
            'reports_params'
        );

        $this->dropTable('reports_params');
    }
}
