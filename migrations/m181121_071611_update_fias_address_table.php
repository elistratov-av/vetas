<?php

use app\commands\migrate\Migration;

/**
 * Class m181121_071611_update_fias_address_table
 */
class m181121_071611_update_fias_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('fias_address', 'houseguid', 'uuid');
        $this->addColumn('fias_address', 'roomguid', 'uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('fias_address', 'houseguid');
        $this->dropColumn('fias_address', 'roomguid');
    }

}
