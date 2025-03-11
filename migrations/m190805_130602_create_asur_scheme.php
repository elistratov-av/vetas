<?php

use app\commands\migrate\Migration;

/**
 * Class m190805_130602_create_asur_scheme
 */
class m190805_130602_create_asur_scheme extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA asur;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA asur;');
    }
}
