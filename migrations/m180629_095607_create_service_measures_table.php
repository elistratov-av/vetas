<?php

use yii\db\Migration;

/**
 * Handles the creation of table `service_measures`.
 */
class m180629_095607_create_service_measures_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('service_measures', [
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
        $this->dropTable('service_measures');
    }
}
