<?php

use app\commands\migrate\Migration;

/**
 * Class m210812_055109_balance_action_transfer_date
 */
class m210812_055109_balance_action_transfer_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \app\models\db\tmc\BalanceAction::tableName(),
            'transfer_date',
            $this->date()->comment('Фактическая дата передачи')
        );
        $this->addColumn(
            \app\models\db\tmc\BalanceAction::tableName(),
            'receiving_date',
            $this->date()->comment('Фактическая дата получения')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            \app\models\db\tmc\BalanceAction::tableName(),
            'transfer_date'
        );
        $this->dropColumn(
            \app\models\db\tmc\BalanceAction::tableName(),
            'receiving_date'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210812_055109_balance_action_transfer_date cannot be reverted.\n";

        return false;
    }
    */
}
