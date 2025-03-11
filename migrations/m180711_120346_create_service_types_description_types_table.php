<?php

use yii\db\Migration;

/**
 * Handles the creation of table `service_types_description_types`.
 * Has foreign keys to the tables:
 *
 * - `description_types`
 * - `service_types`
 */
class m180711_120346_create_service_types_description_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('service_types_description_types', [
            'id_description_type' => $this->integer(),
            'id_service_type' => $this->integer(),
        ]);
        $this->addPrimaryKey(
            'service_types_description_types_pkey',
            'service_types_description_types',
            ['id_description_type', 'id_service_type']
        );

        // creates index for column `id_description_type`
        $this->createIndex(
            'idx-service_types_description_types-id_description_type',
            'service_types_description_types',
            'id_description_type'
        );

        // add foreign key for table `description_types`
        $this->addForeignKey(
            'fk-service_types_description_types-id_description_type',
            'service_types_description_types',
            'id_description_type',
            'description_types',
            'id',
            'CASCADE'
        );

        // creates index for column `id_service_type`
        $this->createIndex(
            'idx-service_types_description_types-id_service_type',
            'service_types_description_types',
            'id_service_type'
        );

        // add foreign key for table `service_types`
        $this->addForeignKey(
            'fk-service_types_description_types-id_service_type',
            'service_types_description_types',
            'id_service_type',
            'service_types',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `description_types`
        $this->dropForeignKey(
            'fk-service_types_description_types-id_description_type',
            'service_types_description_types'
        );

        // drops index for column `id_description_type`
        $this->dropIndex(
            'idx-service_types_description_types-id_description_type',
            'service_types_description_types'
        );

        // drops foreign key for table `service_types`
        $this->dropForeignKey(
            'fk-service_types_description_types-id_service_type',
            'service_types_description_types'
        );

        // drops index for column `id_service_type`
        $this->dropIndex(
            'idx-service_types_description_types-id_service_type',
            'service_types_description_types'
        );

        $this->dropTable('service_types_description_types');
    }
}
