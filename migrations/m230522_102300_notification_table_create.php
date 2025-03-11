<?php

use app\commands\migrate\Migration;

/**
 * Class m230522_102300_notification_table_create
 */
class m230522_102300_notification_table_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.notifications', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->comment('Тема уведомления'),
            'text' => $this->string(255)->comment('Текст уведомления'),
            'id_user' => $this->integer()->notNull(),
            'read' => $this->boolean()->defaultValue(false),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);
        $this->addCommentOnTable('notifications', 'Таблица уведомлений пользователей');
       
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('notifications');

        return true;
    }
}