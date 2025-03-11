<?php

use app\commands\migrate\Migration;

/**
 * Class m190708_095742_drop_pet_owners_id_area_id_district_trg
 */
class m190708_095742_drop_pet_owners_id_area_id_district_trg extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('drop trigger IF EXISTS  pet_owners_id_area_id_district_trg on pet_owners');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190708_095742_drop_pet_owners_id_area_id_district_trg cannot be reverted.\n";

        return false;
    }
}
