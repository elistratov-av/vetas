<?php

use app\commands\migrate\Migration;

/**
 * Class m190415_190340_add_rbac_rules_descriptions_templates
 */
class m190415_190340_add_rbac_rules_descriptions_templates extends Migration
{
    private static $permissionData = [
        // Шаблоны описаний (дополнительная валидация на права - в модели)
        'data.descriptions-templates.manage' => [
            'descr' => 'Шаблоны описаний: CUD',
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
                'sysAdminGos',
                'managementGos',
            ],
        ],
        'activity.visits.descriptions-templates' => [
            'descr' => 'Управление приемом: работа с шаблонами',
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
                'sysAdminGos',
                'managementGos',
            ],
        ],
        'data.descriptions-templates.manage.menu' => [
            'descr' => 'Шаблоны описаний: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
                'vetSpecGosAmb',
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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190415_190340_add_rbac_rules_descriptions_templates cannot be reverted.\n";

        return false;
    }
    */
}
