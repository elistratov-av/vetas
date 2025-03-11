<?php

use yii\db\Migration;

/**
 * Handles the creation of table `gov_services_params`.
 * Has foreign keys to the tables:
 *
 * - `params`
 * - `gov_services`
 */
class m180717_193410_create_gov_services_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('gov_services_params', [
            'id' => $this->primaryKey(),
            'id_param' => $this->integer()->notNull(),
            'id_service' => $this->integer()->notNull(),
            'req_in' => $this->boolean()->defaultValue(false),
            'req_out' => $this->boolean()->defaultValue(true),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_param`
        $this->createIndex(
            'idx-gov_services_params-id_param',
            'gov_services_params',
            'id_param'
        );

        // add foreign key for table `params`
        $this->addForeignKey(
            'fk-gov_services_params-id_param',
            'gov_services_params',
            'id_param',
            'params',
            'id',
            'CASCADE'
        );

        // creates index for column `id_service`
        $this->createIndex(
            'idx-gov_services_params-id_service',
            'gov_services_params',
            'id_service'
        );

        // add foreign key for table `gov_services`
        $this->addForeignKey(
            'fk-gov_services_params-id_service',
            'gov_services_params',
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
        // drops foreign key for table `params`
        $this->dropForeignKey(
            'fk-gov_services_params-id_param',
            'gov_services_params'
        );

        // drops index for column `id_param`
        $this->dropIndex(
            'idx-gov_services_params-id_param',
            'gov_services_params'
        );

        // drops foreign key for table `gov_services`
        $this->dropForeignKey(
            'fk-gov_services_params-id_service',
            'gov_services_params'
        );

        // drops index for column `id_service`
        $this->dropIndex(
            'idx-gov_services_params-id_service',
            'gov_services_params'
        );

        $this->dropTable('gov_services_params');
    }
}
