<?php

use yii\db\Migration;

/**
 * Handles the creation of table `newsletter_type`.
 */
class m230925_142727_create_newsletter_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('newsletter_type', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'name' => $this->string()
        ]);

        $this->insert('newsletter_type', [
            'name' => 'Для пользователей',
            'type' => 'newsletter_user'
        ]);
        $this->insert('newsletter_type', [
            'name' => 'Оповещение о событии',
            'type' => 'newsletter_event'
        ]);
        $this->insert('newsletter_type', [
            'name' => 'Напоминание о предстоящем приеме',
            'type' => 'newsletter_reception'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('newsletter_type');
    }
}
