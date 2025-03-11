<?php

use app\commands\migrate\Migration;

/**
 * Class m240828_081902_create_archive_schema
 */
class m240828_081902_create_archive_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA IF NOT EXISTS archive');
    }

    /**
     * {@inheritdoc}
     */
    
    public function safeDown()
    {
        $this->execute('DROP SCHEMA IF EXISTS archive CASCADE');
    }
}
