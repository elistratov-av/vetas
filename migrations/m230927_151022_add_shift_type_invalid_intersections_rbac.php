<?php

use app\commands\migrate\Migration;

/**
 * Class m230927_151022_add_shift_type_invalid_intersections_rbac
 */
class m230927_151022_add_shift_type_invalid_intersections_rbac extends Migration
{
    private static $permissionData = [
        'data.shift_type_invalid_intersections.user' => [
            'descr' => 'Недопустимые пересечения типов смен: получение данных',
            'roles' => [
                'callCenterOperator',
                'dispatcher',
                'foundPetModerator',
                'inspector',
                'inspectorReadonly',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'managementShelter',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'shelterActivityAdmin',
                'shelterAnimalSocializationSpecialist',
                'shelterFaunaMonitoringSpecialist',
                'shelterSysAdmin',
                'shelterVeterinarian',
                'specShelter',
                'sysAdminGos',
                'sysAdminPrivFull',
                'technicMto',
                'vetSpecGos',
                'vetSpecGosAmb',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'vetSpecVaccination',
            ],
        ],
        'data.shift_type_invalid_intersections.admin' => [
            'descr' => 'Недопустимые пересечения типов смен: модификация данных',
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
            fwrite(STDOUT, "process permission: $permissionName\n");
            $description = $data['descr'];
            $permission = $auth->getPermission($permissionName);
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $description;
                $auth->add($permission);
            }
            if (!empty($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    fwrite(STDOUT, "process role: $roleName\n");
                    $role = $auth->getRole($roleName);
                    if (!$auth->hasChild($role, $permission)) {
                        $auth->addChild($role, $permission);
                    }
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
