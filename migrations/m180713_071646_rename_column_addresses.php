<?php

use yii\db\Migration;

/**
 * Handles adding name to table `addresses`.
 */
class m180713_071646_rename_column_addresses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('alter table addresses rename column address to name;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('alter table addresses rename column name to address;');
    }
}
