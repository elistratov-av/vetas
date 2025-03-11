<?php

use app\commands\migrate\Migration;

/**
 * Class m200820_065157_found_pet_messages
 */
class m200820_065157_found_pet_messages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('found_pet.messages', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'service_number' => $this->string(),
            'body' => $this->json(),
            'headers' => $this->json(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0)
        ]);

        $this->createIndex('idx_found_pet_messages_type', 'found_pet.messages', 'type');
        $this->createIndex('idx_found_pet_messages_service_number', 'found_pet.messages', 'service_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('found_pet.messages');
    }
}
