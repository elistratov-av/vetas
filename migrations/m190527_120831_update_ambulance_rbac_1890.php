<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190527_120831_update_ambulance_rbac_1890
 */
class m190527_120831_update_ambulance_rbac_1890 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        $roleDispatcher = $auth->getRole(Role::ROLE_DISPATCHER);

        $permissionNames = [
            'data.pets.manage.menu',
            'data.owners.manage.menu',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->addChild($roleDispatcher, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = $this->getAuthManager();

        $roleDispatcher = $auth->getRole(Role::ROLE_DISPATCHER);

        $permissionNames = [
            'data.pets.manage.menu',
            'data.owners.manage.menu',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($roleDispatcher, $permission);
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
