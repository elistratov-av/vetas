<?php

use app\commands\migrate\Migration;

/**
 * Class m180912_101433_add_col_sort_by_to_breeds
 */
class m180912_101433_add_col_sort_by_to_breeds extends Migration
{
    public function safeUp()
    {
        $this->addColumn('breeds', 'sort_by', $this->integer());
        $this->addCommentOnColumn('breeds', 'sort_by', 'Сортировка');

        $this->update('breeds',['sort_by' => 1]);
        $this->update('breeds',['sort_by' => 2],['name' => "Другая"]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('breeds', 'sort_by');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180912_101433_add_col_sort_by_to_breeds cannot be reverted.\n";

        return false;
    }
    */
}
