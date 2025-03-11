<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%faq2_to_group}}`.
 */
class m230922_133809_create_faq2_to_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('faq2_to_group', [
            'id' => $this->primaryKey(),
            'id_faq2' => $this->integer()->notNull(),
            'id_faq2_group' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_faq2_to_group-id_faq2',
            'faq2_to_group',
            'id_faq2',
            'faq2',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_faq2_to_group-id_faq2_group',
            'faq2_to_group',
            'id_faq2_group',
            'faq2_group',
            'id',
            'CASCADE'
        );

        $this->addCommentOnTable('faq2_to_group', 'Связь раздела FAQ с FAQ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_faq2_to_group-id_faq2', 'faq2_to_group');
        $this->dropForeignKey('fk_faq2_to_group-id_faq2_group', 'faq2_to_group');

        $this->dropTable('faq2_to_group');
    }
}
