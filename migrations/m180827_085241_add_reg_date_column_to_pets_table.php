<?php

use yii\db\Migration;

/**
 * Handles adding reg_date to table `pets`.
 */
class m180827_085241_add_reg_date_column_to_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pets', 'reg_date', $this->date());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pets', 'reg_date');
    }
}
