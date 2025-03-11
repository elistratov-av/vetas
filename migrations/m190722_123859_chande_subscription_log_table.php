<?php

use app\commands\migrate\Migration;

/**
 * Class m190722_123859_chande_subscription_log_table
 */
class m190722_123859_chande_subscription_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('subscription.log');
        $this->createTable('subscription.log', [
            'id' => $this->bigPrimaryKey(),
            'log_time' => $this->dateTime()->comment('Время отправки события в ИС ПК'),
            'event_id' => $this->string()->comment('Идентификатор события'),
            'event_code' => $this->string()->comment('Код события'),
            'to' => $this->string()->comment('Контакт для отпавки'),
            'params' => $this->json()->comment('Параметры запроса указанные при отправке'),
            'is_success' => $this->boolean()->defaultValue(false)->comment('Статус отправки события в ИС ПК'),
            'error' => $this->text()->comment('Текст ошибки')
        ]);

        $this->createIndex('idx-subscription_log-is_success', 'subscription.log', 'is_success');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('subscription.log');
        $this->createTable('subscription.log', [
            'id' => $this->bigPrimaryKey(),
            'log_time' => $this->dateTime(),
            'event_id' => $this->string(),
            'event_code' => $this->string(),
            'params' => $this->json(),
            'message' => $this->text(),
            'is_success' => $this->boolean()->defaultValue(false)
        ]);

        $this->createIndex('idx-subscription_log-is_success', 'subscription.log', 'is_success');
    }

}
