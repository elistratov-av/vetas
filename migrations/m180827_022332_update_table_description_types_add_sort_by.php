<?php

use app\commands\migrate\Migration;

/**
 * Class m180827_022332_update_table_description_types_add_sort_by
 */
class m180827_022332_update_table_description_types_add_sort_by extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('description_types', 'sort_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('description_types', 'sort_by');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_022332_update_table_description_types_add_sort_by cannot be reverted.\n";

        return false;
    }
    */
}
