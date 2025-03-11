<?php

use yii\db\Migration;

/**
 * Handles the creation of table `content_of_active_substances`.
 * Has foreign keys to the tables:
 *
 * - `tmc_types`
 * - `active_substances`
 * - `measures`
 */
class m180612_165054_create_content_of_active_substances_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('content_of_active_substances', [
            'id' => $this->primaryKey(),
            'unit' => $this->string()->notNull(),
            'id_tmc' => $this->integer(),
            'id_active_substance' => $this->integer(),
            'id_measure' => $this->integer(),
        ]);

        // creates index for column `id_active_substance`
        $this->createIndex(
            'idx-content_of_active_substances-id_active_substance',
            'content_of_active_substances',
            'id_active_substance'
        );

        // add foreign key for table `active_substances`
        $this->addForeignKey(
            'fk-content_of_active_substances-id_active_substance',
            'content_of_active_substances',
            'id_active_substance',
            'active_substances',
            'id',
            'CASCADE'
        );

        // creates index for column `id_measure`
        $this->createIndex(
            'idx-content_of_active_substances-id_measure',
            'content_of_active_substances',
            'id_measure'
        );

        // add foreign key for table `measures`
        $this->addForeignKey(
            'fk-content_of_active_substances-id_measure',
            'content_of_active_substances',
            'id_measure',
            'measures',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `active_substances`
        $this->dropForeignKey(
            'fk-content_of_active_substances-id_active_substance',
            'content_of_active_substances'
        );

        // drops index for column `id_active_substance`
        $this->dropIndex(
            'idx-content_of_active_substances-id_active_substance',
            'content_of_active_substances'
        );

        // drops foreign key for table `measures`
        $this->dropForeignKey(
            'fk-content_of_active_substances-id_measure',
            'content_of_active_substances'
        );

        // drops index for column `id_measure`
        $this->dropIndex(
            'idx-content_of_active_substances-id_measure',
            'content_of_active_substances'
        );

        $this->dropTable('content_of_active_substances');
    }
}
