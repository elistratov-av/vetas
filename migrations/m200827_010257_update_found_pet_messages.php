<?php

use app\commands\migrate\Migration;

/**
 * Class m200827_010257_update_found_pet_messages
 */
class m200827_010257_update_found_pet_messages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('found_pet.messages', 'is_success', $this->boolean());
        $this->addColumn('found_pet.messages', 'ad_errors', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('found_pet.messages', 'is_success');
        $this->dropColumn('found_pet.messages', 'ad_errors');
    }
}
