<?php

use app\commands\migrate\Migration;

/**
 * Class m200924_104417_found_pet_addresses_drop_region
 */
class m200924_104417_found_pet_addresses_drop_region extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('found_pet.ad_addresses', 'region');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200924_104417_found_pet_addresses_drop_region cannot be reverted.\n";

        return false;
    }
}
