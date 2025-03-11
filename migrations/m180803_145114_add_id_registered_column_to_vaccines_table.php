<?php

use yii\db\Migration;

/**
 * Handles adding id_registered to table `vaccines`.
 */
class m180803_145114_add_id_registered_column_to_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('vaccines', 'id_registered', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('vaccines', 'id_registered');
    }
}
