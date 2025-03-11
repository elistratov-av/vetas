<?php

use yii\db\Migration;

/**
 * Handles dropping id_active_substance from table `vaccines`.
 */
class m180803_145112_drop_id_active_substance_column_from_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('vaccines', 'id_active_substance');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('vaccines', 'id_active_substance', $this->integer());
    }
}
