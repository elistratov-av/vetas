<?php

use app\commands\migrate\Migration;

/**
 * Class m210817_080345_create_table_subscription_log_pets
 */
class m210817_080345_create_table_subscription_log_pets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('subscription.log_pets', [
            'id' => $this->primaryKey(),
            'id_log' => $this->integer(),
            'id_pet' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('subscription.log_pets');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210817_080345_create_table_subscription_log_pets cannot be reverted.\n";

        return false;
    }
    */
}
