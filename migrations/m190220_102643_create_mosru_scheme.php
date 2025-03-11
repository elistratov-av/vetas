<?php

use app\commands\migrate\Migration;

/**
 * Class m190220_102643_create_new_scheme
 */
class m190220_102643_create_mosru_scheme extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA IF NOT EXISTS mosru');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA IF EXISTS mosru');
    }
}
