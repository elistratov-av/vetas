<?php

use app\commands\migrate\Migration;

/**
 * Class m191204_055709_1964_odopm_fix_odopm_districts
 */
class m191204_055709_1964_odopm_fix_odopm_districts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('odopm.odopm_districts', 'id_area', 'area_bti_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('odopm.odopm_districts', 'area_bti_code', 'id_area');
    }
}
