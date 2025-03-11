<?php

use app\commands\migrate\Migration;

/**
 * Class m181203_072215_add_columns_to_fias_address
 */
class m181203_072215_add_columns_to_fias_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('fias_address', 'cityguid', 'uuid');
        $this->addColumn('fias_address', 'streetguid', 'uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('fias_address', 'cityguid');
        $this->dropColumn('fias_address', 'streetguid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181203_072215_add_columns_to_fias_address cannot be reverted.\n";

        return false;
    }
    */
}
