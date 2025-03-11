<?php

use app\commands\migrate\Migration;

/**
 * Class m230928_175340_add_shift_type_ref_rbac
 */
class m230928_175340_add_shift_type_ref_rbac extends Migration
{
    private static $permissionData = [
        'data.shift_type_ref.user' => [
            'descr' => 'Типы смен ref: получение данных',
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
        'data.shift_type_ref.admin' => [
            'descr' => 'Типы смен ref: модификация данных',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
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
