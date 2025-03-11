<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cabinet_types`.
 */
class m180627_093549_create_cabinet_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cabinet_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'description' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cabinet_types');
    }
}
