<?php

use app\commands\migrate\Migration;

/**
 * Class m190820_103656_create_reference_odopm_tables
 */
class m190820_103656_create_reference_odopm_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('odopm.references', [
            'id' => $this->primaryKey(),
            'id_reference' => $this->integer(),
            'id_value' => $this->integer(),
            'name' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm.references');
    }
}
