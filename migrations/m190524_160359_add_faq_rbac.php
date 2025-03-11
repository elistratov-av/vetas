<?php

use app\commands\migrate\Migration;

/**
 * Class m190527_070339_add_faq_rbac
 */
class m190524_160359_add_faq_rbac extends Migration
{
    private static $permissionData = [
        // Faq
        'data.faqs.manage.menu' => [
            'descr' => 'Справочная информация: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        'data.faqs.manage' => [
            'descr' => 'Справочная информация: получение данных',
            'roles' => [
                'registryGos',
                'vetSpecGos',
                'sysAdminGos',
                'managementGos',

            ],
        ],
        'data.faqs.manage.W' => [
            'descr' => 'Справочная информация: управление:CUD',
            'roles' => [
                'sysAdminGos',
                'managementGos',
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
