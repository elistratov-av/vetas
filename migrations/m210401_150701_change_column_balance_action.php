<?php

use app\commands\migrate\Migration;

/**
 * Class m210401_150701_change_column_balance_action
 */
class m210401_150701_change_column_balance_action extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('tmc.balance_action_tmc_list', 'write_off_reason');
        $this->addColumn('tmc.balance_action', 'write_off_reason', $this->string('30')->comment('Типы причины списания'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.balance_action', 'write_off_reason');
        $this->addColumn('tmc.balance_action_tmc_list', 'write_off_reason', $this->string('30')->comment('Типы причины списания'));
    }
}
