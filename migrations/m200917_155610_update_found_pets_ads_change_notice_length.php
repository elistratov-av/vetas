<?php

use app\commands\migrate\Migration;

/**
 * Class m200917_155610_update_found_pets_ads_change_notice_length
 */
class m200917_155610_update_found_pets_ads_change_notice_length extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('found_pet.ads', 'notice', $this->string(500));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200917_155610_update_found_pets_ads_change_notice_length cannot be reverted.\n";

        return false;
    }
}
