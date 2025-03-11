<?php

use app\commands\migrate\Migration;

/**
 * Class m191202_101346_1964_fix_odopm_attributes_specification
 */
class m191202_101346_1964_fix_odopm_attributes_specification extends Migration
{
    private $tableName = 'odopm.odopm_attributes_specification';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, 'name', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn($this->tableName, 'name', $this->string(32));
    }
}
