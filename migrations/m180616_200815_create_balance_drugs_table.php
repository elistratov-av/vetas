<?php

use yii\db\Migration;

/**
 * Handles the creation of table `balance_drugs`.
 * Has foreign keys to the tables:
 *
 * - `organizations`
 * - `drugs`
 */
class m180616_200815_create_balance_drugs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('balance_drugs', [
            'id' => $this->primaryKey(),
            'dose_count' => $this->integer()->notNull(),
            'inventory_number' => $this->string()->unique()->notNull(),
            'registration_date' => $this->date()->notNull(),
            'id_organization' => $this->integer(),
            'id_drug' => $this->integer(),
        ]);

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-balance_drugs-id_organization',
            'balance_drugs',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-balance_drugs-id_organization',
            'balance_drugs',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_drug`
        $this->createIndex(
            'idx-balance_drugs-id_drug',
            'balance_drugs',
            'id_drug'
        );

        // add foreign key for table `drugs`
        $this->addForeignKey(
            'fk-balance_drugs-id_drug',
            'balance_drugs',
            'id_drug',
            'drugs',
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
            'fk-balance_drugs-id_organization',
            'balance_drugs'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-balance_drugs-id_organization',
            'balance_drugs'
        );

        // drops foreign key for table `drugs`
        $this->dropForeignKey(
            'fk-balance_drugs-id_drug',
            'balance_drugs'
        );

        // drops index for column `id_drug`
        $this->dropIndex(
            'idx-balance_drugs-id_drug',
            'balance_drugs'
        );

        $this->dropTable('balance_drugs');
    }
}
