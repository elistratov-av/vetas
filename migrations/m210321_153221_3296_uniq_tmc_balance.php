<?php

use app\commands\migrate\Migration;

/**
 * Class m210321_153221_3296_uniq_tmc_balance
 */
class m210321_153221_3296_uniq_tmc_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * Только одна строка на каждого
         * Если отличается ценой - то несколько строк
         */
        $this->createIndex(
            'uniq-tmc_balance-type_tmc-id_organization-id_specialist-inventory_number-price',
            'tmc.balance',
            ['type_tmc', 'id_organization', 'id_specialist', 'inventory_number', 'price'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP INDEX "tmc"."uniq-tmc_balance-type_tmc-id_organization-id_specialist-inventory_number-price"');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210321_153221_3296_uniq_tmc_balance cannot be reverted.\n";

        return false;
    }
    */
}
