<?php

use app\commands\migrate\Migration;

/**
 * Class m200128_093008_1964_odopm_fix_organizations_4
 */
class m200128_093008_1964_odopm_fix_organizations_4 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'resp_department');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('organizations', 'resp_department', $this->integer());
    }
}
