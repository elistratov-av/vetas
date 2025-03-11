<?php

use yii\db\Migration;

/**
 * Handles the creation of table `specializations`.
 */
class m180618_130639_create_specializations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('specializations', [
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
        $this->dropTable('specializations');
    }
}
