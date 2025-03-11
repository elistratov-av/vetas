<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m191030_104909_2500_fix_permissions_to_edit_specialists
 */
class m191030_104909_2500_fix_permissions_to_edit_specialists extends Migration
{
    private $tableName = 'public.auth_item_child';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $roleNames = [
            Role::ROLE_SYSADMIN_GOS,
            Role::ROLE_MANAGEMENT_GOS,
            Role::ROLE_REGISTRY_GOS,
            Role::ROLE_VET_SPECIALIST_GOS,
        ];

        foreach ($roleNames as $roleName) {
            $this->insert($this->tableName, [
                'parent' => $roleName,
                'child' => 'data.specialists.manage.menu',
            ]);
        }

        $this->delete($this->tableName, ['child' => 'data.specialists.manage.W']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191030_104909_2500_fix_permissions_to_edit_specialists cannot be reverted.\n";

        return false;
    }
}
