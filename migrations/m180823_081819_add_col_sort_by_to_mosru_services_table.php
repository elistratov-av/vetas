<?php

use yii\db\Migration;

/**
 * Class m180823_081819_add_col_sort_by_to_mosru_services_table
 */
class m180823_081819_add_col_sort_by_to_mosru_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('mosru_services', 'sort_by', $this->integer());
        $this->addCommentOnColumn('mosru_services', 'sort_by', 'Сортировка');

        $this->update('mosru_services',['sort_by' => 1]);
        $this->update('mosru_services',['sort_by' => 2],['name' => "Иные исследования"]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('mosru_services', 'sort_by');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180823_081819_add_col_sort_by_to_mosru_services_table cannot be reverted.\n";

        return false;
    }
    */
}
