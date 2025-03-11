<?php

use app\commands\migrate\Migration;

/**
 * Class m191001_125941_params_inventory_number_fix
 */
class m191001_125941_params_inventory_number_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('params', ['name' => 'Серия вакцины №'], ['tech_name' => 'P0_Inventorynumber']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191001_125941_params_inventory_number_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191001_125941_params_inventory_number_fix cannot be reverted.\n";

        return false;
    }
    */
}
