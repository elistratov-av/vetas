<?php

use yii\db\Migration;

/**
 * Handles the creation of table `identification_types`.
 */
class m180629_073543_create_identification_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('identification_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->unique()->notNull(),
            'description' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('identification_types');
    }
}
