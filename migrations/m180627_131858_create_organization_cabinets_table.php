<?php

use yii\db\Migration;

/**
 * Handles the creation of table `organization_cabinets`.
 * Has foreign keys to the tables:
 *
 * - `organizations`
 * - `cabinet_types`
 */
class m180627_131858_create_organization_cabinets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organization_cabinets', [
            'id' => $this->primaryKey(),
            'cabinet_count' => $this->integer()->notNull(),
            'id_organization' => $this->integer(),
            'id_cabinet_type' => $this->integer(),
        ]);

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-organization_cabinets-id_organization',
            'organization_cabinets',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-organization_cabinets-id_organization',
            'organization_cabinets',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_cabinet_type`
        $this->createIndex(
            'idx-organization_cabinets-id_cabinet_type',
            'organization_cabinets',
            'id_cabinet_type'
        );

        // add foreign key for table `cabinet_types`
        $this->addForeignKey(
            'fk-organization_cabinets-id_cabinet_type',
            'organization_cabinets',
            'id_cabinet_type',
            'cabinet_types',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-organization_cabinets-id_organization',
            'organization_cabinets'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-organization_cabinets-id_organization',
            'organization_cabinets'
        );

        // drops foreign key for table `cabinet_types`
        $this->dropForeignKey(
            'fk-organization_cabinets-id_cabinet_type',
            'organization_cabinets'
        );

        // drops index for column `id_cabinet_type`
        $this->dropIndex(
            'idx-organization_cabinets-id_cabinet_type',
            'organization_cabinets'
        );

        $this->dropTable('organization_cabinets');
    }
}
