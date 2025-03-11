<?php

use yii\db\Migration;

/**
 * Handles dropping id_specialist from table `visits`.
 */
class m180806_141503_drop_id_specialist_column_from_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('visits', 'id_specialist');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('visits', 'id_specialist', $this->integer());
    }
}
