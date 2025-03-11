<?php

use app\commands\migrate\Migration;

/**
 * Class m191205_094415_1964_odopm_fix_organizations_3
 */
class m191205_094415_1964_odopm_fix_organizations_3 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('odopm.odopm_organizations', 'created_at');
        $this->dropColumn('odopm.odopm_organizations', 'updated_at');
        $this->addColumn('odopm.odopm_organizations', 'vet_organization', $this->boolean()->defaultValue(false));
        $this->addColumn('odopm.odopm_organizations', 'created_at', $this->dateTime(0));
        $this->addColumn('odopm.odopm_organizations', 'updated_at', $this->dateTime(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('odopm.odopm_organizations', 'vet_organization');
    }
}
