<?php

use yii\db\Migration;

/**
 * Handles the creation of table `areas`.
 */
class m180619_074323_create_areas_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('areas', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'short_name' => $this->string()->notNull(),
            'area' => $this->integer()->notNull(),
            'bti_code' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('areas');
    }
}
