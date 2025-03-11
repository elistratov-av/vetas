<?php

use app\commands\migrate\Migration;

/**
 * Class m190917_114659_add_mdm_tables
 */
class m190917_114659_add_mdm_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA mdm');
        $this->createTable('mdm.log', [
            'id' => $this->primaryKey(),
            'xml' => $this->text(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('mdm.log');
        $this->execute('DROP SCHEMA mdm');
    }

}
