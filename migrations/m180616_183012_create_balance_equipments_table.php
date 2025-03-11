<?php

use yii\db\Migration;

/**
 * Handles the creation of table `balance_equipments`.
 * Has foreign keys to the tables:
 *
 * - `organizations`
 * - `equipments`
 */
class m180616_183012_create_balance_equipments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('balance_equipments', [
            'id' => $this->primaryKey(),
            'inventory_number' => $this->string()->unique()->notNull(),
            'registration_date' => $this->date()->notNull(),
            'manufactured_number' => $this->string()->notNull(),
            'equipment_condition' => $this->string()->notNull(),
            'id_organization' => $this->integer(),
            'id_equipment' => $this->integer(),
        ]);

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-balance_equipments-id_organization',
            'balance_equipments',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-balance_equipments-id_organization',
            'balance_equipments',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_equipment`
        $this->createIndex(
            'idx-balance_equipments-id_equipment',
            'balance_equipments',
            'id_equipment'
        );

        // add foreign key for table `equipments`
        $this->addForeignKey(
            'fk-balance_equipments-id_equipment',
            'balance_equipments',
            'id_equipment',
            'equipments',
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
            'fk-balance_equipments-id_organization',
            'balance_equipments'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-balance_equipments-id_organization',
            'balance_equipments'
        );

        // drops foreign key for table `equipments`
        $this->dropForeignKey(
            'fk-balance_equipments-id_equipment',
            'balance_equipments'
        );

        // drops index for column `id_equipment`
        $this->dropIndex(
            'idx-balance_equipments-id_equipment',
            'balance_equipments'
        );

        $this->dropTable('balance_equipments');
    }
}
