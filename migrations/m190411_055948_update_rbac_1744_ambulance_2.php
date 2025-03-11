<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190411_055948_update_rbac_1744_ambulance_2
 */
class m190411_055948_update_rbac_1744_ambulance_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        // добавим пока минимальные права сисадмину, администрации, регистратуре

        $roleNames = [
            Role::ROLE_SYSADMIN_GOS,
            Role::ROLE_MANAGEMENT_GOS,
            Role::ROLE_REGISTRY_GOS,
        ];

        $permissionNames = [
            'ambulance.visits.manage',
            'ambulance.visits.manage.menu',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            foreach ($roleNames as $roleName) {
                $role = $auth->getRole($roleName);
                $auth->addChild($role, $permission);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = $this->getAuthManager();

        $permissionNames = [
            'ambulance.visits.manage',
            'ambulance.visits.manage.menu',
        ];

        $roleNames = [
            Role::ROLE_SYSADMIN_GOS,
            Role::ROLE_MANAGEMENT_GOS,
            Role::ROLE_REGISTRY_GOS,
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            foreach ($roleNames as $roleName) {
                $role = $auth->getRole($roleName);
                $auth->removeChild($role, $permission);
            }
        }
    }

    /**
     * @return \app\common\components\rbac\DbManager
     */
    private function getAuthManager()
    {
        return \Yii::$app->authManager;
    }
}
