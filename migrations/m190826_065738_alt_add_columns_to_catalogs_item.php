<?php

use app\commands\migrate\Migration;

/**
 * Class m190826_065738_alt_add_columns_to_catalogs_item
 */
class m190826_065738_alt_add_columns_to_catalogs_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('odopm.odopm_catalogs_item', 'entry_state', $this->integer());
        $this->addColumn('odopm.odopm_catalogs_item', 'entry_delete_reason', $this->integer());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('odopm.odopm_catalogs_item', 'entry_state');
        $this->dropColumn('odopm.odopm_catalogs_item', 'entry_delete_reason');
    }
}
