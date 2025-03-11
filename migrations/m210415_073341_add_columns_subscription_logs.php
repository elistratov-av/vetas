<?php

use app\commands\migrate\Migration;

/**
 * Class m210415_073341_add_columns_subscription_logs
 */
class m210415_073341_add_columns_subscription_logs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log', 'author_id', $this->integer());
        $this->addColumn('subscription.log', 'violation_id', $this->integer());
        $this->addColumn('subscription.log', 'status_push', $this->string());
        $this->addColumn('subscription.log', 'status_email', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.log', 'author_id');
        $this->dropColumn('subscription.log', 'violation_id');
        $this->dropColumn('subscription.log', 'status_push');
        $this->dropColumn('subscription.log', 'status_email');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210415_073341_add_columns_subscription_logs cannot be reverted.\n";

        return false;
    }
    */
}
