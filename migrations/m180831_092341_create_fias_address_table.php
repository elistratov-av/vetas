<?php

use yii\db\Migration;

/**
 * Handles the creation of table `fias_address`.
 */
class m180831_092341_create_fias_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('fias_address', [
            'id' => $this->primaryKey(),
            'full_address' => $this->text(),
            'aoguid' => 'uuid',
            'region' => $this->string(),
            'city' => $this->string(),
            'street' => $this->string(),
            'house' => $this->string(),
            'room' => $this->string(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        $this->createIndex('idx_aoguid', 'fias_address', 'aoguid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('fias_address');
    }
}
