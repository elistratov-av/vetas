<?php

use app\commands\migrate\Migration;

/**
 * Class m230925_160000_add_faq2_rbac
 */
class m230927_130600_add_pet_hotel_rbac extends Migration
{
    private static $permissionData = [
        // Faq
        'data.pet-hotel.menu' => [
            'descr' => 'Зоогостиницы: доступность пункта меню',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'sysAdminGos',
                'vetSpecGos',
            ],
        ],
        'data.pet-hotel.user' => [
            'descr' => 'Зоогостиницы: получение данных',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        'data.pet-hotel.admin' => [
            'descr' => 'Зоогостиницы: модификация данных',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'sysAdminGos',
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
