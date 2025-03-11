<?php

use app\commands\migrate\Migration;

/**
 * Class m190216_091045_empty_rbac_tables
 */
class m190216_091045_empty_rbac_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = [
            'auth_assignment',
            'auth_item_child',
            'auth_item',
            'auth_rule',
        ];
        foreach ($tables as $tableName) {
            $this->delete($tableName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
