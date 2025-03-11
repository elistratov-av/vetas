<?php

use app\commands\migrate\Migration;

/**
 * Class m190717_142456_create_table_elk_log
 */
class m190717_142456_create_table_elk_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elk.log', [
            'id' => $this->primaryKey(),
            'xml' => $this->text(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('elk.log');
    }
}
