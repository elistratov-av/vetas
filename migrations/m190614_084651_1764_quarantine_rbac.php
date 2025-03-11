<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190614_084651_1764_quarantine_rbac
 */
class m190614_084651_1764_quarantine_rbac extends Migration
{
    private static $permissionData = [
        'quarantine.manage.menu' => 'Карантин: доступность пункта меню',
        'quarantine.manage.quarantine.W' => 'Управление карантином на территории',
        'quarantine.manage.animals.W' => 'Контроль животных на территории карантина',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        $role = $auth->getRole(Role::ROLE_INSPECTOR);

        foreach (self::$permissionData as $permissionName => $description) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $description;
            $auth->add($permission);
            $auth->addChild($role, $permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = $this->getAuthManager();

        $role = $auth->getRole(Role::ROLE_INSPECTOR);

        foreach (self::$permissionData as $permissionName => $description) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($role, $permission);
            $auth->remove($permission);
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
