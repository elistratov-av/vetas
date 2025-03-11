<?php

use yii\db\Migration;

/**
 * Handles adding id_violation_history to table `subscription_log`.
 */
class m210817_081303_add_id_violation_history_column_to_subscription_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log','id_violation_history', $this->integer()->comment('История нарушения по отправке данного оповещения'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.log', 'id_violation_history');
    }
}
