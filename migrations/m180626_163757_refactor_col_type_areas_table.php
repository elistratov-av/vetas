<?php

use yii\db\Migration;

/**
 * Class m180626_163757_refactor_col_type_areas_table
 */
class m180626_163757_refactor_col_type_areas_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('areas', 'area', $this->double());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180626_163757_refactor_col_type_areas_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180626_163757_refactor_col_type_areas_table cannot be reverted.\n";

        return false;
    }
    */
}
