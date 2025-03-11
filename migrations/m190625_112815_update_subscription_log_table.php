<?php

use app\commands\migrate\Migration;

/**
 * Class m190625_112815_update_subscription_log_table
 */
class m190625_112815_update_subscription_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log', 'is_success', $this->boolean()->defaultValue(false));
        $this->createIndex('idx-subscription_log-is_success', 'subscription.log', 'is_success');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.log', 'is_success');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190625_112815_update_subscription_log_table cannot be reverted.\n";

        return false;
    }
    */
}
