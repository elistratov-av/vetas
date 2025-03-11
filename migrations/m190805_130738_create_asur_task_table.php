<?php

use yii\db\Migration;

/**
 * Handles the creation of table `asur_task`.
 */
class m190805_130738_create_asur_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('asur.task', [
            'id' => $this->primaryKey(),
            'message_id' => $this->string()->unique()->notNull(),
            'task_id' => $this->string()->unique()->notNull(),
            'task_number' => $this->string()->unique()->notNull(),
            'number' => $this->integer()->notNull(),
            'id_owner' => $this->integer(),
            'file' => $this->string()->defaultValue(null),
            'status' => $this->string(1),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk-asur_task-id_owner',
            'asur.task',
            'id_owner',
            'pet_owners',
            'id',
            'CASCADE'
        );

        $this->createTable('asur.task_number', [
            'year' => $this->smallInteger()->unique(),
            'number' => $this->integer()
        ]);
        $this->addCommentOnTable('asur.task_number', 'Служебная таблица для генерации правильной последовательности номеров для образений в АС УР');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-asur_task-id_owner', 'asur.task');
        $this->dropTable('asur.task');
        $this->dropTable('asur.task_number');
    }
}
