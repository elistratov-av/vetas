<?php

use app\commands\migrate\Migration;

/**
 * Class m191204_062155_1964_odopm_fix_odopm_catalogs
 */
class m191204_062155_1964_odopm_fix_odopm_catalogs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('odopm.odopm_catalogs', 'updated_at');
        $this->addColumn('odopm.odopm_catalogs', 'created_at', $this->dateTime(0));
        $this->addColumn('odopm.odopm_catalogs', 'updated_at', $this->dateTime(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('odopm.odopm_catalogs', 'created_at');
        $this->dropColumn('odopm.odopm_catalogs', 'updated_at');
        $this->addColumn('odopm.odopm_catalogs', 'updated_at', $this->date());
    }
}
