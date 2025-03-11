<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_102816_add_gosvetnadzor_rbac
 */
class m190605_102816_add_gosvetnadzor_rbac extends Migration
{
    private static $permissionData = [
        // gosvetnadzor
        'data.gosvetnadzor.manage.menu' => [
            'descr' => 'Госветнадзор: доступность пункта меню',
            'roles' => [
                'inspector',
            ],
        ],
        'data.gosvetnadzor.violation_admin_rights' => [
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
        ],
        'data.gosvetnadzor.violation_admin_rights.W' => [
            'descr' => 'Справочник АПН: CUD',
            'roles' => [
                'sysAdminGos',
                'inspector',
                'sysAdminPrivFull',
            ],
        ],
        'data.gosvetnadzor.violation_type' => [
            'descr' => 'Справочник тип нарушения: поиск',
            'roles' => [
                'vetSpecGos',
                'inspector',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        'gosvetnadzor.violation.manage.W' => [
            'descr' => 'Контроль нарушений, история нарушений',
            'roles' => [
                'inspector',
            ],
        ],
        'activity.visits.violation.report' => [
            'descr' => 'Приемы: подача данных о нарушении',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
            'rule_name' => 'VisitSpecialistRule',
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
        echo "m190605_102816_add_gosvetnadzor_rbac cannot be reverted.\n";

        return false;
    }
    */
}
