<?php

use app\commands\migrate\Migration;

/**
 * Class m210405_204300_alter_column_for_balance_actions
 */
class m210405_204300_alter_column_for_balance_actions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.visit_service_tmc', 'count_production_form', $this->decimal(11, 2)->comment('Количество в формах производства'));
        $this->addColumn('public.visit_service_tmc', 'count_utilize', $this->decimal(11, 2)->comment('Количество утилизорованого ТМЦ'));
        $this->update('public.visit_service_tmc', ['count_utilize' => 0]);

        $this->renameColumn('tmc.balance_action_tmc_list', 'count_in_production_forms', 'count_production_form');
        $this->addColumn('tmc.balance_action_tmc_list', 'count_utilize', $this->decimal(11, 2)->comment('Количество утилизорованого ТМЦ'));
        $this->update('tmc.balance_action_tmc_list', ['count_utilize' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.visit_service_tmc', 'count_production_form');
        $this->dropColumn('public.visit_service_tmc', 'count_utilize');
        $this->renameColumn('tmc.balance_action_tmc_list', 'count_production_form', 'count_in_production_forms');
        $this->dropColumn('tmc.balance_action_tmc_list', 'count_utilize');
    }
}
