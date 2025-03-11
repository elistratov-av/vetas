<?php

use yii\db\Migration;

/**
 * Handles the creation of table `asur_queue`.
 */
class m190816_150116_create_asur_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('asur.queue', [
            'id' => $this->primaryKey(),
            'channel' => $this->string()->notNull(),
            'job' => $this->binary()->notNull(),
            'pushed_at' => $this->integer()->notNull(),
            'reserved_at' => $this->integer(),
            'done_at' => $this->integer(),
            'delay' => $this->integer()->notNull(),
            'ttr' => $this->integer()->notNull(),
            'attempt' => $this->integer(),
            'priority' => $this->integer()->unsigned()->notNull()->defaultValue(1024)
        ]);

        $this->createIndex('idx-asur_queue-channel', 'asur.queue', 'channel');
        $this->createIndex('idx-asur_queue-reserved_at', 'asur.queue', 'reserved_at');
        $this->createIndex('idx-asur_queue-priority', 'asur.queue', 'priority');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('asur.idx-asur_queue-channel', 'asur.queue');
        $this->dropIndex('asur.idx-asur_queue-reserved_at', 'asur.queue');
        $this->dropIndex('asur.idx-asur_queue-priority', 'asur.queue');

        $this->dropTable('asur.queue');
    }
}
