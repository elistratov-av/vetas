<?php

use app\commands\migrate\Migration;


/**
 * Handles the creation of table `animalid_log`.
 */
class m190826_114024_create_animalid_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('animalid.logs', [
                'id' => $this->bigPrimaryKey(),
                'level' => $this->integer(),
                'category' => $this->string(),
                'log_time' => "timestamp without time zone NOT NULL default now()::timestamp without time zone",
                'prefix' => $this->text(),
                'message' => $this->text(),
            ]);

        $this->createIndex('idx_animalid-logs_level', 'animalid.logs', 'level');
        $this->createIndex('idx_animalid-logs_category', 'animalid.logs', 'category');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('animalid.logs');
    }
}
