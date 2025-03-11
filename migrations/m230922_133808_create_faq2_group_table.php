<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%faq2_group}}`.
 */
class m230922_133808_create_faq2_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('faq2_group', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'title' => $this->string()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->createIndex('faq2_group_code_uniq_index', 'faq2_group', 'code', true);

        $this->addCommentOnTable('faq2_group', 'Разделы FAQ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('faq2_group');
    }
}
