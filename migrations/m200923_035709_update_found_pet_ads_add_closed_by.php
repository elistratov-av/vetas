<?php

use app\commands\migrate\Migration;

/**
 * Class m200923_035709_update_found_pet_ads_add_closed_by
 */
class m200923_035709_update_found_pet_ads_add_closed_by extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('found_pet.ads', 'closed_by', $this->string()->comment('AUTHOR - зыкрыто пользователем, MODERATOR - модератором, AUTO - истек срок публикации'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('found_pet.ads', 'closed_by');
    }
}
