<?php

use app\models\db\Pets;
use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal`.
 */
class m231022_214200_create_pet_hotel_request_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_hotel_request', [
            'id' => $this->primaryKey(),
            'id_status' => $this->integer(),
            'id_owner' => $this->integer(),
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
            'pet_hotel_request',
            'id_status',
            'pet_hotel_request_status',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_pet_hotel_request-id_owner',
            'pet_hotel_request',
            'id_owner',
            'pet_owners',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_pet_hotel_request-id_animal',
            'pet_hotel_request',
            'id_animal',
            'pets',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_pet_hotel_request-id_room',
            'pet_hotel_request',
            'id_room',
            'pet_hotel_room',
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
        $this->dropTable('pet_hotel_request');
    }
}
