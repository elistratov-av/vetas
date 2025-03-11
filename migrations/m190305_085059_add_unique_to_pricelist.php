<?php

use app\commands\migrate\Migration;

/**
 * Class m190305_085059_add_unique_to_pricelist
 */
class m190305_085059_add_unique_to_pricelist extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE pricelists ADD CONSTRAINT pricelist_organization UNIQUE (id_organization)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE pricelists DROP CONSTRAINT pricelist_organization");
    }

}
