<?php

use app\commands\migrate\Migration;


/**
 * Handles the creation of table `aninmalid_converter`.
 */
class m190826_114023_create_aninmalid_converter_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('animalid.converter', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'field' => $this->string(),
            'value_from' => $this->string(),
            'value_to' => $this->string(),
            'dir' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('aninmalid_converter');
    }
}
