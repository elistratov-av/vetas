<?php

use app\commands\migrate\Migration;

/**
 * Class m241022_144601_update_pet_owners_table
 */
class m241022_144601_update_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('pet_owners', 'description', $this->string(400));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('pet_owners', 'description', $this->string(255));
    }
}
