<?php

use app\commands\migrate\Migration;


/**
 * Handles the dropping of table `aniamlid_queue`.
 */
class m190826_114026_drop_aniamlid_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('animalid.queue');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
