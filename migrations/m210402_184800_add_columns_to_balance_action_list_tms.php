<?php

use app\commands\migrate\Migration;

/**
 * Class m210402_184800_add_columns_to_balance_action_list_tms
 */
class m210402_184800_add_columns_to_balance_action_list_tms extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.balance_action_tmc_list', 'write_off_pack_form', $this->boolean()->comment('Флаг: Списать целиком по форме производства'));
        $this->update('tmc.balance_action_tmc_list', ['write_off_pack_form' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.balance_action_tmc_list', 'write_off_pack_form');
    }
}
