<?php

use app\commands\migrate\Migration;

/**
 * Class m190610_102816_add_gosvetnadzor_violation_admin_rights_menu_rbac
 */
class m190610_102816_add_gosvetnadzor_violation_admin_rights_menu_rbac extends Migration
{
    private static $permissionData = [
        'data.gosvetnadzor.violation_admin_rights.menu' => [
            'descr' => 'Справочник АПН: поиск',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
                //'vetSpecGosAmb',
                //'dispatcher',
                'inspector',

                'sysAdminPrivFull',
                'managementPrivFull',
                'managementPrivMin',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
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
            $rule_name = empty($data['rule_name']) ? null : $data['rule_name'];
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $description;
                $permission->ruleName = $rule_name;
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
        echo "m190610_102816_add_gosvetnadzor_violation_admin_rights_menu_rbac cannot be reverted.\n";

        return false;
    }
    */
}
