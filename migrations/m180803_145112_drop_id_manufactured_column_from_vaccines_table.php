<?php

use yii\db\Migration;

/**
 * Handles dropping id_manufactured from table `vaccines`.
 */
class m180803_145112_drop_id_manufactured_column_from_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('vaccines', 'id_manufactured', 'id_produced');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('vaccines', 'id_produced', 'id_manufactured');
    }
}
