<?php

use yii\db\Migration;

/**
 * Handles dropping id_file_packaging_image from table `vaccines`.
 */
class m180803_145112_drop_id_file_packaging_image_column_from_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('vaccines', 'id_file_packaging_image');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('vaccines', 'id_file_packaging_image', $this->integer());
    }
}
