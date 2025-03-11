<?php

use yii\db\Migration;

/**
 * Handles the creation of table `subscription_information`.
 */
class m190728_130735_create_subscription_information_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('subscription.information', [
            'id' => $this->primaryKey(),
            'type' => $this->string(32)->notNull()->comment('Тип уведомления'),
            'pet_id' => $this->integer()->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'date' => $this->timestamp(0)
        ]);

        $this->addForeignKey(
            'fk-subscription_information-pet_id',
            'subscription.information',
            'pet_id',
            'pets',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-subscription_information-owner_id',
            'subscription.information',
            'owner_id',
            'pet_owners',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-subscription_information-inform',
            'subscription.information',
            ['type', 'owner_id', 'pet_id']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('subscription.idx-subscription_information-inform', 'subscription.information');
        $this->dropTable('subscription.information');
    }
}
