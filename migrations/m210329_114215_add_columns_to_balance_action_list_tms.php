<?php

use app\commands\migrate\Migration;

/**
 * Class m210329_114215_add_columns_to_balance_action_list_tms
 */
class m210329_114215_add_columns_to_balance_action_list_tms extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.balance_action_tmc_list', 'id_dosage', $this->integer()->comment('ID дозировки (если выбрано списание в дозировке)')->after('id_specialist'));
        $this->addColumn('tmc.balance_action_tmc_list', 'comment', $this->string(255)->comment('Комментарий'));
        $this->addColumn('tmc.balance_action_tmc_list', 'count_selected', $this->decimal(11, 2)->comment('Значение кол-во введенное пользователем'));
        $this->addColumn('tmc.balance_action_tmc_list', 'write_off_all', $this->boolean()->comment('Флаг: Списать целиком'));
        $this->addColumn('tmc.balance_action_tmc_list', 'write_off_reason', $this->string()->comment('Типы причины списания'));

        // TMC balance_action_tmc_list
        $this->addForeignKey(
            'fk-balance_action_tmc_list-id_dosage-tmc_balance',
            'tmc.balance_action_tmc_list',
            'id_dosage',
            'tmc.dosages',
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.balance_action_tmc_list', 'id_dosage');
        $this->dropColumn('tmc.balance_action_tmc_list', 'comment');
        $this->dropColumn('tmc.balance_action_tmc_list', 'write_off_reason');
        $this->dropColumn('tmc.balance_action_tmc_list', 'count_selected');
        $this->dropColumn('tmc.balance_action_tmc_list', 'write_off_all');
    }
}
