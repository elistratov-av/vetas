<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_request`.
 */
class m230925_122350_create_pet_hotel_request_table extends Migration
{
    const PET_HOTEL_REQUEST_TABLE = 'pet_hotel_request';
    const PET_HOTEL_REQUEST_STATUS_TABLE = 'pet_hotel_request_status';
    const PET_HOTEL_ANIMAL_TABLE = 'pet_hotel_animal';
    const PET_HOTELS_ROOM_TABLE = 'pet_hotel_room';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_REQUEST_TABLE, [
            'id' => $this->primaryKey(),
            'id_status' => $this->integer(),
            'id_animal' => $this->integer(),
            'id_room' => $this->integer(),
            'date_from' => $this->timestamp(0),
            'date_to' => $this->timestamp(0),
            'notes' => $this->text(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel_request-id_status',
            self::PET_HOTEL_REQUEST_TABLE,
            'id_status',
            self::PET_HOTEL_REQUEST_STATUS_TABLE,
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_pet_hotel_request-id_animal',
            self::PET_HOTEL_REQUEST_TABLE,
            'id_animal',
            self::PET_HOTEL_ANIMAL_TABLE,
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_pet_hotel_request-id_room',
            self::PET_HOTEL_REQUEST_TABLE,
            'id_room',
            self::PET_HOTELS_ROOM_TABLE,
            'id',
            'NO ACTION',
            'NO ACTION'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::PET_HOTEL_REQUEST_TABLE);
    }
}
