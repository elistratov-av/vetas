<?php

use app\commands\migrate\Migration;

/**
 * Handles the creation of table `{{%faq}}`.
 */
class m190524_160308_create_faq_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('faq', [
            'id' => $this->primaryKey(),
            'question' => $this->text()->notNull(),
            'answer' => $this->text()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->createIndex('faq_question_uniq_index', 'faq', 'question', true);

        $this->addCommentOnTable('faq', 'Ведение справочной информации 
         для владельцев животных со ссылками на НПА. По сути FAQ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('faq');
    }
}
