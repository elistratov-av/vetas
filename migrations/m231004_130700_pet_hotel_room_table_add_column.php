<?php

use app\commands\migrate\Migration;

/**
 * Class m230630_124900_pets_table_add_column
 */
class m231004_130700_pet_hotel_room_table_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pet_hotel_room', 'purpose', $this->text()->comment('Назначение помещения'));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.pet_hotel_room', 'purpose');

        return true;
    }
}
