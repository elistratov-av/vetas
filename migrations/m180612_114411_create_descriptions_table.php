<?php

use yii\db\Migration;

/**
 * Handles the creation of table `descriptions`.
 * Has foreign keys to the tables:
 *
 * - `description_types`
 */
class m180612_114411_create_descriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('descriptions', [
            'id' => $this->primaryKey(),
            'entity_type' => $this->string()->unique()->notNull(),
            'entity_id' => $this->integer()->unique()->notNull(),
            'description' => $this->string()->unique()->notNull(),
            'id_description_type' => $this->integer(),
        ]);

        // creates index for column `id_description_type`
        $this->createIndex(
            'idx-descriptions-id_description_type',
            'descriptions',
            'id_description_type'
        );

        // add foreign key for table `description_types`
        $this->addForeignKey(
            'fk-descriptions-id_description_type',
            'descriptions',
            'id_description_type',
            'description_types',
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
            'fk-descriptions-id_description_type',
            'descriptions'
        );

        // drops index for column `id_description_type`
        $this->dropIndex(
            'idx-descriptions-id_description_type',
            'descriptions'
        );

        $this->dropTable('descriptions');
    }
}
