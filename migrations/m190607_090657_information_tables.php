<?php

use app\commands\migrate\Migration;

/**
 * Class m190607_090657_information_tables
 */
class m190607_090657_information_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE SCHEMA subscription");

        $this->createTable('subscription.confirm', [
            'id' => $this->primaryKey(),
            'token' => $this->string()->unique()->notNull(),
            'id_contact' => $this->integer()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);

        $this->addForeignKey(
            'fk-subscription_confirm-id_contact',
            'subscription.confirm',
            'id_contact',
            'contacts',
            'id',
            'CASCADE'
        );

        $this->createTable('subscription.subscriptions', [
            'id' => $this->primaryKey(),
            'id_contact' => $this->integer()->notNull(),
            'subscribed' => $this->boolean()->defaultValue(false),
            'subscription_id' => $this->integer()->comment('ID подписки на стороне ИС ПК'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);
        $this->addForeignKey(
            'fk-subscriptions-id_contact',
            'subscription.subscriptions',
            'id_contact',
            'contacts',
            'id',
            'CASCADE'
        );

        $this->addColumn('contacts', 'confirmed', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-subscription_confirm-id_contact', 'subscription.confirm');
        $this->dropTable('subscription.confirm');

        $this->dropForeignKey('fk-subscriptions-id_contact', 'subscription.subscriptions');
        $this->dropTable('subscription.subscriptions');

        $this->dropColumn('contacts', 'confirmed');

        $this->execute('DROP SCHEMA subscription');
    }
}
