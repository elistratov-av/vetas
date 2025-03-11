<?php

use yii\db\Migration;

/**
 * Handles the creation of table `subscription_template`.
 */
class m210428_073516_alter_subscription_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('subscription.log', 'click_count_email', $this->integer()->defaultValue(0)->comment('Количество переходов по ссылке email'));
        $this->addColumn('subscription.log', 'click_count_push', $this->integer()->defaultValue(0)->comment('Количество переходов по ссылке push'));
        $this->addColumn('subscription.log', 'is_unsubscribed_email', $this->boolean()->defaultValue(false)->comment('Призведена отписка от рассылки email'));
        $this->addColumn('subscription.log', 'is_unsubscribed_push', $this->boolean()->defaultValue(false)->comment('Призведена отписка от рассылки push'));

        $this->dropColumn('subscription.log', 'author_id');
        $this->dropColumn('subscription.log', 'violation_id');
        $this->addColumn('subscription.log', 'id_author', $this->integer()->comment('Автор закрытия нарушения'));
        $this->addColumn('subscription.log', 'id_violation', $this->integer()->comment('ID закрытого нарушения'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('subscription.log', 'click_count_email');
        $this->dropColumn('subscription.log', 'click_count_push');
        $this->dropColumn('subscription.log', 'is_unsubscribed_email');
        $this->dropColumn('subscription.log', 'is_unsubscribed_push');

        $this->dropColumn('subscription.log', 'id_author');
        $this->dropColumn('subscription.log', 'id_violation');
        $this->addColumn('subscription.log', 'author_id', $this->integer());
        $this->addColumn('subscription.log', 'violation_id', $this->integer());
    }
}
