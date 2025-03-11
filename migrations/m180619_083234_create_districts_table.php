<?php

use yii\db\Migration;

/**
 * Handles the creation of table `districts`.
 */
class m180619_083234_create_districts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('districts', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'parent_id' => $this->integer()->notNull(),
            'bti_code' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('districts');
    }
}
