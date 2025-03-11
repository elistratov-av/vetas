<?php

use yii\db\Migration;

/**
 * Handles the creation of table `addresses`.
 * Has foreign keys to the tables:
 *
 * - `areas`
 * - `districts`
 */
class m180619_085834_create_addresses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('addresses', [
            'id' => $this->primaryKey(),
            'address' => $this->string()->notNull(),
            'latitude' => $this->string()->notNull(),
            'longitude' => $this->string()->notNull(),
            'id_area' => $this->integer(),
            'id_district' => $this->integer(),
        ]);

        // creates index for column `id_area`
        $this->createIndex(
            'idx-addresses-id_area',
            'addresses',
            'id_area'
        );

        // add foreign key for table `areas`
        $this->addForeignKey(
            'fk-addresses-id_area',
            'addresses',
            'id_area',
            'areas',
            'id',
            'CASCADE'
        );

        // creates index for column `id_district`
        $this->createIndex(
            'idx-addresses-id_district',
            'addresses',
            'id_district'
        );

        // add foreign key for table `districts`
        $this->addForeignKey(
            'fk-addresses-id_district',
            'addresses',
            'id_district',
            'districts',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `areas`
        $this->dropForeignKey(
            'fk-addresses-id_area',
            'addresses'
        );

        // drops index for column `id_area`
        $this->dropIndex(
            'idx-addresses-id_area',
            'addresses'
        );

        // drops foreign key for table `districts`
        $this->dropForeignKey(
            'fk-addresses-id_district',
            'addresses'
        );

        // drops index for column `id_district`
        $this->dropIndex(
            'idx-addresses-id_district',
            'addresses'
        );

        $this->dropTable('addresses');
    }
}
