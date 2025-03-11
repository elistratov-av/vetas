<?php

use app\commands\migrate\Migration;

/**
 * Class m210701_084624_alter_subscription_log
 */
class m210701_084624_alter_subscription_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log', 'id_owner', $this->integer()->comment('id владельца'));
        $this->addColumn('subscription.log', 'id_initiator', $this->integer()->comment('id инициатора отправки. Null - если отправлено по крону'));
        $this->addColumn('subscription.log', 'id_pet', $this->integer()->comment('id животного'));
        $this->addColumn('subscription.log', 'id_visit', $this->integer()->comment('id приема'));
        $this->createIndex('idx_id_owner', 'subscription.log', 'id_owner');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //$this->dropIndex('idx_id_owner', 'subscription.log');
        $this->dropColumn('subscription.log', 'id_owner');
        $this->dropColumn('subscription.log', 'id_initiator');
        $this->dropColumn('subscription.log', 'id_pet');
        $this->dropColumn('subscription.log', 'id_visit');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210701_084624_alter_subscription_log cannot be reverted.\n";

        return false;
    }
    */
}
