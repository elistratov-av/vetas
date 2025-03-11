<?php

use app\models\db\Pets;
use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal`.
 */
class m231021_131100_create_pet_hotel_animal_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.pet_hotel_animal', [
            'id_pet' => $this->integer(),
            'processing' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel_animal-id_pet',
            'public.pet_hotel_animal',
            'id_pet',
            Pets::tableName(),
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
        $this->dropTable('public.pet_hotel_animal');
        return true;
    }
}
