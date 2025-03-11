<?php

use yii\db\Migration;

/**
 * Handles the creation of table `subscription_log`.
 */
class m190614_132841_create_subscription_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('subscription.log', [
            'id' => $this->bigPrimaryKey(),
            'log_time' => $this->dateTime(),
            'event_id' => $this->string(),
            'event_code' => $this->string(),
            'params' => $this->json(),
            'message' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('subscription.log');
    }
}
