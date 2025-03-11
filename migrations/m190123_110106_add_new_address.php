<?php

use app\commands\migrate\Migration;

/**
 * Class m190123_110106_add_new_address
 */
class m190123_110106_add_new_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('addresses', [
            'name' => 'поселение Московский, деревня Саларьево, улица Картмазовская, владение 46Б',
            'id_area' => 8,
            'id_district' => 73,
            'latitude' => '55.620461',
            'longitude' => '37.426363'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
