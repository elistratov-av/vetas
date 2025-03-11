<?php

use app\commands\migrate\Migration;

/**
 * Class m190701_082740_add_fias_addresses_field
 */
class m190701_082740_add_fias_addresses_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('fias_addresses', 'regionguid', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('fias_addresses', 'regionguid');
    }
}
