<?php

use yii\db\Migration;

/**
 * Handles the creation of table `dictionaries`.
 */
class m181001_193608_create_dictionaries_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('dictionaries', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'type' => $this->string()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->createIndex('idx-dictionaries-type', 'dictionaries', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('dictionaries');
    }
}
