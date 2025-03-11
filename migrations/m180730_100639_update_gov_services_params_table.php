<?php

use yii\db\Migration;

/**
 * Class m180730_100639_update_gov_services_params_table
 */
class m180730_100639_update_gov_services_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gov_services_params', 'sort_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gov_services_params', 'sort_by');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_100639_update_gov_services_params_table cannot be reverted.\n";

        return false;
    }
    */
}
