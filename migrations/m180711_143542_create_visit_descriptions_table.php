<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_descriptions`.
 * Has foreign keys to the tables:
 *
 * - `visits`
 * - `description_types`
 */
class m180711_143542_create_visit_descriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_descriptions', [
            'id' => $this->primaryKey(),
            'description' => $this->string()->notNull(),
            'id_visit' => $this->integer(),
            'id_description_type' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        // creates index for column `id_visit`
        $this->createIndex(
            'idx-visit_descriptions-id_visit',
            'visit_descriptions',
            'id_visit'
        );

        // add foreign key for table `visits`
        $this->addForeignKey(
            'fk-visit_descriptions-id_visit',
            'visit_descriptions',
            'id_visit',
            'visits',
            'id',
            'CASCADE'
        );

        // creates index for column `id_description_type`
        $this->createIndex(
            'idx-visit_descriptions-id_description_type',
            'visit_descriptions',
            'id_description_type'
        );

        // add foreign key for table `description_types`
        $this->addForeignKey(
            'fk-visit_descriptions-id_description_type',
            'visit_descriptions',
            'id_description_type',
            'description_types',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `visits`
        $this->dropForeignKey(
            'fk-visit_descriptions-id_visit',
            'visit_descriptions'
        );

        // drops index for column `id_visit`
        $this->dropIndex(
            'idx-visit_descriptions-id_visit',
            'visit_descriptions'
        );

        // drops foreign key for table `description_types`
        $this->dropForeignKey(
            'fk-visit_descriptions-id_description_type',
            'visit_descriptions'
        );

        // drops index for column `id_description_type`
        $this->dropIndex(
            'idx-visit_descriptions-id_description_type',
            'visit_descriptions'
        );

        $this->dropTable('visit_descriptions');
    }
}
