<?php

use yii\db\Migration;

/**
 * Class m180724_012155_refactor_params_table
 */
class m180724_012155_refactor_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('params', 'param_config', 'config');
        $this->addColumn('params', 'visit_flag', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180724_012155_refactor_params_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_012155_refactor_params_table cannot be reverted.\n";

        return false;
    }
    */
}
