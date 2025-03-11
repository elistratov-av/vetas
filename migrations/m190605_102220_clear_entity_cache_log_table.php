<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%entity_cache_log}}`.
 */
class m190605_102220_clear_entity_cache_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('entity_cache_log');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
