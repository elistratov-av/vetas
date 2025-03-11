<?php

use app\models\db\PetOwners;
use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal_owner`.
 */
class m231021_130700_create_pet_hotel_animal_owner_table extends Migration
{

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->createTable('public.pet_hotel_animal_owner', [
            'id_owner' => $this->integer(),
            'phone_number' => $this->string(),
            'address' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel_animal-id_owner',
            'public.pet_hotel_animal_owner',
            'id_owner',
            PetOwners::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.pet_hotel_animal_owner');
        return true;
    }
}
