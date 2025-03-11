<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal`.
 */
class m230925_122122_create_pet_hotel_animal_table extends Migration
{
    const PET_HOTEL_ANIMAL_TABLE = 'pet_hotel_animal';
    const PET_HOTEL_ANIMAL_OWNER_TABLE = 'pet_hotel_animal_owner';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_ANIMAL_TABLE, [
            'id' => $this->primaryKey(),
            'id_owner' => $this->integer(),
            'nickname' => $this->string(),
            'type' => $this->string(),
            'breed' => $this->string(),
            'gender_male' => $this->boolean()->defaultValue(true),
            'processing' => $this->text(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel_animal-id_owner',
            self::PET_HOTEL_ANIMAL_TABLE,
            'id_owner',
            self::PET_HOTEL_ANIMAL_OWNER_TABLE,
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
        $this->dropTable(self::PET_HOTEL_ANIMAL_TABLE);
    }
}
