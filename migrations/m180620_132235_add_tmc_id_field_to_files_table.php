<?php

use yii\db\Migration;

/**
 * Class m180620_132235_add_tmc_id_field_to_files_table
 */
class m180620_132235_add_tmc_id_field_to_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('files', 'id_tmc', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('files', 'id_tmc');
    }
}
