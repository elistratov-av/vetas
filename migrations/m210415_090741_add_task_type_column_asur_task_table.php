<?php

use app\commands\migrate\Migration;

/**
 * Class m210415_090741_add_task_type_column_asur_task_table
 */
class m210415_090741_add_task_type_column_asur_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('asur.task', 'task_type', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('asur.task', 'task_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210415_090741_add_task_type_column_asur_task_table cannot be reverted.\n";

        return false;
    }
    */
}
