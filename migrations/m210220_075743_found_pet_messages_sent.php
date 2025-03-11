<?php

use app\commands\migrate\Migration;

/**
 * Class m210220_075743_found_pet_messages_sent
 */
class m210220_075743_found_pet_messages_sent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('found_pet.messages_sent', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'service_number' => $this->string(),
            'request' => $this->json(),
            'response' => $this->json(),
            'response_headers' => $this->json(),
            'response_code' => $this->integer(),
            'curl_error' => $this->string(),
            'user_error' => $this->string(),
            'created_at' => $this->dateTime(0),
        ]);

        $this->createIndex('idx_found_pet_messages_sent_type', 'found_pet.messages_sent', 'type');
        $this->createIndex('idx_found_pet_messages_sent_service_number', 'found_pet.messages_sent', 'service_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('found_pet.messages_sent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210220_075743_found_pet_messages_sended cannot be reverted.\n";

        return false;
    }
    */
}
