<?php

use app\commands\migrate\Migration;

/**
 * Class m190419_084853_create_elk_scheme
 */
class m190419_084853_create_elk_scheme extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA elk;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP SCHEMA elk;');
    }
}
