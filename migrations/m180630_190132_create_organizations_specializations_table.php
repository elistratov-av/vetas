<?php

use yii\db\Migration;

/**
 * Handles the creation of table `organizations_specializations`.
 * Has foreign keys to the tables:
 *
 * - `specializations`
 * - `organizations`
 */
class m180630_190132_create_organizations_specializations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organizations_specializations', [
            'id' => $this->primaryKey(),
            'id_specialization' => $this->integer(),
            'id_organization' => $this->integer(),
        ]);

        // creates index for column `id_specialization`
        $this->createIndex(
            'idx-organizations_specializations-id_specialization',
            'organizations_specializations',
            'id_specialization'
        );

        // add foreign key for table `specializations`
        $this->addForeignKey(
            'fk-organizations_specializations-id_specialization',
            'organizations_specializations',
            'id_specialization',
            'specializations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-organizations_specializations-id_organization',
            'organizations_specializations',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-organizations_specializations-id_organization',
            'organizations_specializations',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `specializations`
        $this->dropForeignKey(
            'fk-organizations_specializations-id_specialization',
            'organizations_specializations'
        );

        // drops index for column `id_specialization`
        $this->dropIndex(
            'idx-organizations_specializations-id_specialization',
            'organizations_specializations'
        );

        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-organizations_specializations-id_organization',
            'organizations_specializations'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-organizations_specializations-id_organization',
            'organizations_specializations'
        );

        $this->dropTable('organizations_specializations');
    }
}
