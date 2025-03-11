<?php

use app\commands\migrate\Migration;

/**
 * Class m181121_093853_add_id_fias_address_to_pet_owners_table
 */
class m181121_093853_add_id_fias_address_to_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'id_fias_address', $this->integer());
        $this->addCommentOnColumn('pet_owners', 'id_fias_address', 'Ссылка на адрес ФИАС, таблица fias_address');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners', 'id_fias_address');
    }

}
