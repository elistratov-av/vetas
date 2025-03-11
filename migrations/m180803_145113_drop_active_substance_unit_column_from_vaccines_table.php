<?php

use yii\db\Migration;

/**
 * Handles dropping active_substance_unit from table `vaccines`.
 */
class m180803_145113_drop_active_substance_unit_column_from_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('vaccines', 'active_substance_unit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('vaccines', 'active_substance_unit', $this->integer());
    }
}
