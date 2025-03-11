<?php

use yii\db\Migration;

/**
 * Handles the creation of table `files`.
 */
class m180618_101334_create_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('files', [
            'id' => $this->primaryKey(),
            'hash' => $this->string()->notNull(),
            'path' => $this->string()->notNull(),
            'name' => $this->string(),
            'created' => $this->timestamp()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('files');
    }
}
