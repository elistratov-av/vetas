<?php

use app\commands\migrate\Migration;

/**
 * Class m210401_072412_3296_drop_fk_in_archive
 */
class m210401_072412_3296_drop_fk_in_archive extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk_balance_flow_visits_gov_servicesn',
            'tmc_archive.balance_flow'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey(
            'fk_balance_flow_visits_gov_servicesn',
            'tmc_archive.balance_flow',
            'id_visitservice',
            'visits_gov_services',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210401_072412_3296_drop_fk_in_archive cannot be reverted.\n";

        return false;
    }
    */
}
