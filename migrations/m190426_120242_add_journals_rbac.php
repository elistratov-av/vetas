<?php

use app\commands\migrate\Migration;

/**
 * Class m190426_120242_add_journals_rbac
 */
class m190426_120242_add_journals_rbac extends Migration
{
    private static $permissionData = [
        // Журналы
        'data.journals.manage.menu' => [
            'descr' => 'Журналы: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        'data.journals.manage' => [
            'descr' => 'Журналы: получение данных журналов',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $permissionName => $data) {
            $description = $data['descr'];
            $permission = $auth->getPermission($permissionName);
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $description;
                if ($permissionName == 'data.journals.manage') {
                    $rule = new \app\common\components\rbac\rules\AllOrgsCompositeRule1;
                    $permission->ruleName = $rule->name;
                }
                $auth->add($permission);
            }
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->addChild($role, $permission);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $permissionName => $data) {
            $permission = $auth->getPermission($permissionName);
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    $auth->removeChild($role, $permission);
                }
            }
            $auth->remove($permission);
        }
    }
}
