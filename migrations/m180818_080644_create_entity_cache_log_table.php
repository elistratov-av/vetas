<?php

use yii\db\Migration;

/**
 * Handles the creation of table `entity_cache_log`.
 */
class m180818_080644_create_entity_cache_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('entity_cache_log', [
            'id' => $this->primaryKey(),
            'datetime' => $this->timestamp(6),
            'microtime' => $this->float(),
            'url' => $this->string(1000),
            'cache_key' => $this->json(),
            'found' => $this->boolean(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('entity_cache_log');
    }
}
