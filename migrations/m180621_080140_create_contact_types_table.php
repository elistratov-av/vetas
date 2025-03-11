<?php

use yii\db\Migration;

/**
 * Handles the creation of table `contact_types`.
 */
class m180621_080140_create_contact_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contact_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'contact_type' => $this->string(50)->notNull(),
        ]);

        $this->createIndex('idx_contact_type', 'contact_types', 'contact_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_contact_type', 'contact_types');
        $this->dropTable('contact_types');
    }
}
