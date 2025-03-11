<?php

use yii\db\Migration;

/**
 * Handles the creation of table `susbscription_queue`.
 */
class m190607_120343_create_subscription_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('subscription.queue', [
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

        $this->createIndex('idx-subscription_queue-channel', 'subscription.queue', 'channel');
        $this->createIndex('idx-subscription_queue-reserved_at', 'subscription.queue', 'reserved_at');
        $this->createIndex('idx-subscription_queue-priority', 'subscription.queue', 'priority');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('subscription.idx-subscription_queue-channel', 'subscription.queue');
        $this->dropIndex('subscription.idx-subscription_queue-reserved_at', 'subscription.queue');
        $this->dropIndex('subscription.idx-subscription_queue-priority', 'subscription.queue');

        $this->dropTable('subscription.queue');
    }
}
