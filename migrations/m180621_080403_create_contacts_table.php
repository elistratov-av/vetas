<?php

use yii\db\Migration;

/**
 * Handles the creation of table `contacts`.
 * Has foreign keys to the tables:
 *
 * - `contact_types`
 */
class m180621_080403_create_contacts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contacts', [
            'id' => $this->primaryKey(),
            'id_contact_type' => $this->integer()->notNull(),
            'entity_type' => $this->string(50)->notNull(),
            'entity_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
        ]);

        // creates index for column `id_contact_type`
        $this->createIndex(
            'idx_type',
            'contacts',
            'id_contact_type'
        );

        // creates index for column `entity_id`
        $this->createIndex(
            'idx_entity',
            'contacts',
            'entity_type, entity_id'
        );

        // add foreign key for table `contact_types`
        $this->addForeignKey(
            'fk-contacts-id_contact_type',
            'contacts',
            'id_contact_type',
            'contact_types',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `contact_types`
        $this->dropForeignKey(
            'fk-contacts-id_contact_type',
            'contacts'
        );

        // drops index for column `id_contact_type`
        $this->dropIndex(
            'idx_type',
            'contacts'
        );

        $this->dropIndex(
            'idx_entity',
            'contacts'
        );

        $this->dropTable('contacts');
    }
}
