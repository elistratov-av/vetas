<?php

use app\commands\migrate\Migration;

/**
 * Class m200128_093042_1964_odopm_fix_odopm_organizations_actions
 */
class m200128_093042_1964_odopm_fix_odopm_organizations_actions extends Migration
{
    private $tableName = 'odopm.organizations_actions';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn($this->tableName, 'dttm_action', 'action');
        $this->addColumn($this->tableName, 'created_at', $this->dateTime(0));
        $this->addColumn($this->tableName, 'updated_at', $this->dateTime(0));
        $this->addColumn($this->tableName, 'created_by', $this->integer());
        $this->addColumn($this->tableName, 'updated_by', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn($this->tableName, 'action', 'dttm_action');
        $this->dropColumn($this->tableName, 'created_at');
        $this->dropColumn($this->tableName, 'updated_at');
        $this->dropColumn($this->tableName, 'created_by');
        $this->dropColumn($this->tableName, 'updated_by');
    }
}
