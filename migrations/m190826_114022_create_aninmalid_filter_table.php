<?php

use app\commands\migrate\Migration;


/**
 * Handles the creation of table `aninmalid_filter_`.
 */
class m190826_114022_create_aninmalid_filter_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('animalid.filter', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'field' => $this->string(),
            'value' => $this->string(),
            'dir' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('aninmalid_filter_');
    }
}
