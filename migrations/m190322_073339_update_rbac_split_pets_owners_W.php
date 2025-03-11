<?php

use app\commands\migrate\Migration;

/**
 * Class m190322_073339_update_rbac_split_pets_owners_W
 */
class m190322_073339_update_rbac_split_pets_owners_W extends Migration
{
    private static $roles = [
        [
            'role' => 'vetSpecGosAmb',
            'description' => 'Ветеринарный специалист выездной бригады (гос)',
            'ext_description' => 'роль предназначена для ветеринарного специалиста организации, подчиненной «Комитету ветеринарии города Москва», работающего в составе выездной бригады НВП',
        ],
    ];

    private static $permissionData = [
        'data.pets.manage.U' => [
            'descr' => 'Учет животных: управление животным: U',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        'data.owners.manage.U' => [
            'descr' => 'Учет владельцев животных: U',
            'roles' => [
                'vetSpecGos',
            ],
        ],
    ];

    private static $oldPermissions = [
        'data.pets.manage.W',
        'data.owners.manage.W',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        $parentRole = $auth->getRole(\app\common\components\rbac\Role::ROLE_VET_SPECIALIST_GOS);

        foreach (self::$oldPermissions as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($parentRole, $permission);
        }

        foreach (self::$roles as $roleData) {
            $role = $auth->createRole($roleData['role']);
            $role->description = $roleData['description'] . "\n" . $roleData['ext_description'];
            $auth->add($role);
            $auth->addChild($parentRole, $role);
            foreach (self::$oldPermissions as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                $auth->addChild($role, $permission);
            }
        }

        foreach (self::$permissionData as $permissionName => $data) {
            $description = $data['descr'];
            $permission = $auth->createPermission($permissionName);
            $permission->description = $description;
            $auth->add($permission);
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
        echo "m190322_073339_update_rbac_split_pets_owners_W cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190322_073339_update_rbac_split_pets_owners_W cannot be reverted.\n";

        return false;
    }
    */
}
