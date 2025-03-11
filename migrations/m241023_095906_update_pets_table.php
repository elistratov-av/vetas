<?php

use app\commands\migrate\Migration;

/**
 * Class m241023_095906_update_pets_table
 */
class m241023_095906_update_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('pets', 'description', $this->string(400));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('pets', 'description', $this->string(255));
    }
}
