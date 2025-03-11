<?php

use app\commands\migrate\Migration;

/**
 * Handles the creation of table `{{%faq_links}}`.
 */
class m190524_160324_create_faq_links_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('faq_links', [
            'id' => $this->primaryKey(),
            'href' => $this->string(),
            'text' => $this->string()->notNull(),
            'id_faq' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_faq_links-id_faq',
            'faq_links',
            'id_faq',
            'faq',
            'id',
            'CASCADE');

        $this->createIndex('faq_links__text_uniq_index', 'faq_links', 'text', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_faq_links-id_faq', 'faq_links');

        $this->dropTable('faq_links');
    }
}
