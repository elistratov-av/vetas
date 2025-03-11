<?php

use app\commands\migrate\Migration;

/**
 * Class m231009125600_pet_hotel_animal_owner_table_add_column
 */
class m231009_125600_pet_hotel_animal_owner_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pet_hotel_animal_owner', 'address', $this->string()->comment('Адрес владельца'));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pet_hotel_animal_owner', 'address');

        return true;
    }
}
