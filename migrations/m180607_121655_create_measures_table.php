<?php

use yii\db\Migration;

/**
 * Handles the creation of table `measures`.
 */
class m180607_121655_create_measures_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('measures', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'description' => $this->string(255)->notNull(),
        ]);

        $this->createIndex('idx-name_unique', 'measures', 'name', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('measures');
    }
}
