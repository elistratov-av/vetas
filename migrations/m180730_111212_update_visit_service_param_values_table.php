<?php

use yii\db\Migration;

/**
 * Class m180730_111212_update_visit_service_param_values_table
 */
class m180730_111212_update_visit_service_param_values_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visit_service_param_values', 'complex_value', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visit_service_param_values', 'complex_value');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_111212_update_visit_service_param_values_table cannot be reverted.\n";

        return false;
    }
    */
}
