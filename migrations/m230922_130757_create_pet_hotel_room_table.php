<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotels_room`.
 */
class m230922_130757_create_pet_hotel_room_table extends Migration
{
    const PET_HOTEL_ROOM_TABLE = 'pet_hotel_room';
    const PET_HOTEL_TABLE = 'pet_hotel';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_ROOM_TABLE, [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'id_pet_hotel' => $this->integer(),
            'area' => $this->float(),
            'notes' => $this->text(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel_room-id_pet_hotel',
            self::PET_HOTEL_ROOM_TABLE,
            'id_pet_hotel',
            self::PET_HOTEL_TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::PET_HOTEL_ROOM_TABLE);
    }
}
