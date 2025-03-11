<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%faq2}}`.
 */
class m230922_133807_create_faq2_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('faq2', [
            'id' => $this->primaryKey(),
            'question' => $this->text()->notNull(),
            'answer' => $this->text()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->createIndex('faq2_question_uniq_index', 'faq2', 'question', true);

        $this->addCommentOnTable('faq2', 'FAQ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('faq2');
    }
}
