<?php

use app\commands\migrate\Migration;

/**
 * Class m191011_062722_fix_organizations_unom_field
 */
class m191011_062722_fix_organizations_unom_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('organizations', 'UNOM', 'unom');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191011_062722_fix_organizations_unom_field cannot be reverted.\n";

        return false;
    }
}
