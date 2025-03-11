<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m191028_113050_2500_revoke_permisssions_for_specialists_menu
 */
class m191028_113050_2500_revoke_permisssions_for_specialists_menu extends Migration
{
    private $tableName = 'public.auth_item_child';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete($this->tableName, ['child' => 'data.specialists.manage.menu']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
    }
}
