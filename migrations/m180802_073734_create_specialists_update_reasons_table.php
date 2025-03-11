<?php

use yii\db\Migration;

/**
 * Handles the creation of table `specialists_update_reasons`.
 * Has foreign keys to the tables:
 *
 * - `specialists`
 * - `users`
 */
class m180802_073734_create_specialists_update_reasons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('specialists_update_reasons', [
            'id' => $this->primaryKey(),
            'description' => $this->string()->notNull(),
            'id_specialist' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
        ]);

        // creates index for column `id_specialist`
        $this->createIndex(
            'idx-specialists_update_reasons-id_specialist',
            'specialists_update_reasons',
            'id_specialist'
        );

        // add foreign key for table `specialists`
        $this->addForeignKey(
            'fk-specialists_update_reasons-id_specialist',
            'specialists_update_reasons',
            'id_specialist',
            'specialists',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `specialists`
        $this->dropForeignKey(
            'fk-specialists_update_reasons-id_specialist',
            'specialists_update_reasons'
        );

        // drops index for column `id_specialist`
        $this->dropIndex(
            'idx-specialists_update_reasons-id_specialist',
            'specialists_update_reasons'
        );

        $this->dropTable('specialists_update_reasons');
    }
}
