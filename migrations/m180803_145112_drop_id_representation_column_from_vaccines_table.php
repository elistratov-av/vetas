<?php

use yii\db\Migration;

/**
 * Handles dropping id_representation from table `vaccines`.
 */
class m180803_145112_drop_id_representation_column_from_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('vaccines', 'id_representation', 'id_dealer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('vaccines', 'id_dealer', 'id_representation');
    }
}
