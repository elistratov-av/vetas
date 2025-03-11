<?php

use yii\db\Migration;

/**
 * Handles the creation of table `active_substances`.
 */
class m180604_150912_create_active_substances_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('active_substances', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'name_en' => $this->string()->notNull()->unique(),
        ]);

        $this->createIndex('idx_name', 'active_substances', 'name', true);
        $this->createIndex('idx_name_en', 'active_substances', 'name_en', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('active_substances');
    }
}
