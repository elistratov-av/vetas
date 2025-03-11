<?php

use app\commands\migrate\Migration;

/**
 * Class m190603_130820_add_vacpoints_rbac
 */
class m190603_130820_add_vacpoints_rbac extends Migration
{
    private static $permissionData = [
        // vacpoints
        'data.vacpoints.manage.menu' => [
            'descr' => 'Прививочные пункты: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'registryGos',
            ],
        ]
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


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190603_130820_add_vacpoints_rbac cannot be reverted.\n";

        return false;
    }
    */
}
