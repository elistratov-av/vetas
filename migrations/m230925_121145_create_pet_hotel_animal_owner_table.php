<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal_owner`.
 */
class m230925_121145_create_pet_hotel_animal_owner_table extends Migration
{
    const PET_HOTEL_ANIMAL_OWNER_TABLE = 'pet_hotel_animal_owner';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_ANIMAL_OWNER_TABLE, [
            'id' => $this->primaryKey(),
            'i_fio' => $this->string(),
            'o_fio' => $this->string(),
            'f_fio' => $this->string(),
            'phone_number' => $this->string(),
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
        $this->dropTable(self::PET_HOTEL_ANIMAL_OWNER_TABLE);
    }
}
