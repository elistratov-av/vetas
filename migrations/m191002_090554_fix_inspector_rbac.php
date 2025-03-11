<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m191002_090554_fix_inspector_rbac
 */
class m191002_090554_fix_inspector_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('auth_item_child', ['parent' => Role::ROLE_INSPECTOR, 'child' => 'activity.visits.create']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191002_090554_fix_inspector_rbac cannot be reverted.\n";

        return false;
    }
}
