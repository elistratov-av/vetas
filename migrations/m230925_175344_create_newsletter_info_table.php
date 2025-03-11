<?php

use yii\db\Migration;

/**
 * Handles the creation of table `newsletter_info`.
 */
class m230925_175344_create_newsletter_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('newsletter_info', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()->comment('Наименование рассылки'),
            'status' => $this->boolean()->comment('Статус рассылки'),
            'period_from' => $this->dateTime()->comment('Период недоступности системы от'),
            'period_upto' => $this->dateTime()->comment('Период недоступности системы до'),
            'mailing_date' => $this->dateTime()->notNull()->comment('Дата рассылки'),
            'id_organizations' => $this->string(),
            'all_organizations' => $this->boolean(),
            'text' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'user' => $this->integer()
        ]);

        $this->addForeignKey(
            'fk-newsletter_type-id_newsletter_info-type',
            'newsletter_info',
            'type',
            'newsletter_type',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-users-id_newsletter_info-author',
            'newsletter_info',
            'user',
            'users',
            'id',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('newsletter_info');
    }
}
