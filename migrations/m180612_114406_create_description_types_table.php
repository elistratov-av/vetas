<?php

use yii\db\Migration;

/**
 * Handles the creation of table `description_types`.
 */
class m180612_114406_create_description_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('description_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('description_types');
    }
}
