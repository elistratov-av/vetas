<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_request_status`.
 */
class m230925_121843_create_pet_hotel_request_status_table extends Migration
{
    const PET_HOTEL_REQUEST_STATUS_TABLE = 'pet_hotel_request_status';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_REQUEST_STATUS_TABLE, [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
            'name' => $this->string(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::PET_HOTEL_REQUEST_STATUS_TABLE);
    }
}
