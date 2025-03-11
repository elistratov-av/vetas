<?php

use app\commands\migrate\Migration;

/**
 * Class m200820_052032_found_pet_update_ads_add_active_till
 */
class m200820_052032_found_pet_update_ads_add_active_till extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('found_pet.ads', 'active_till', $this->date()->comment('Дата, до которой объявление будет активно'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('found_pet.ads', 'active_till');
    }
}
