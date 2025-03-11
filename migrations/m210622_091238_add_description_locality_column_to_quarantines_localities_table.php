<?php

use yii\db\Migration;

/**
 * Handles adding description_locality to table `quarantines_localities`.
 */
class m210622_091238_add_description_locality_column_to_quarantines_localities_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('quarantines_localities', 'description_locality', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('quarantines_localities', 'description_locality');
    }
}
