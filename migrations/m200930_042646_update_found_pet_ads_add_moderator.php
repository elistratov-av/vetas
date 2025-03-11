<?php

use app\commands\migrate\Migration;

/**
 * Class m200930_042646_update_found_pet_ads_add_moderator
 */
class m200930_042646_update_found_pet_ads_add_moderator extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('found_pet.ads', 'id_specialist', $this->integer()->comment('Модератор объявления'));

        $this->addForeignKey('fk_ads_id_specialist', 'found_pet.ads', 'id_specialist', 'public.specialists', 'id', 'SET NULL', 'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('found_pet.ads', 'id_specialist');
    }
}
