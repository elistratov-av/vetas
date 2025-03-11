<?php

use app\commands\migrate\Migration;

/**
 * Class m190305_114647_update_rbac_permissions_for_menu
 */
class m190305_114647_update_rbac_permissions_for_menu extends Migration
{
    private static $permissionData = [
        // Баланс организации
        'data.organizations.balance.menu' => [
            'descr' => 'Баланс организации: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Поиск организаций
        'data.organizations.manage.menu' => [
            'descr' => 'Поиск организаций: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Поиск животных
        'data.pets.manage.menu' => [
            'descr' => 'Поиск животных: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Владельцы животных
        'data.owners.manage.menu' => [
            'descr' => 'Владельцы животных: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Поиск специалистов
        'data.specialists.manage.menu' => [
            'descr' => 'Поиск специалистов: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Приемы
        'activity.visits.manage.menu' => [
            'descr' => 'Приемы: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        // Типы организаций
        'data.classificators.org_types.menu' => [
            'descr' => 'Типы организаций: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Виды и породы животных
        'data.classificators.species-breeds.menu' => [
            'descr' => 'Виды и породы животных: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Заболевания
        'data.classificators.diseases.menu' => [
            'descr' => 'Заболевания: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Причины снятия с регистрационного учета
        'data.classificators.reg_expire_reasons.menu' => [
            'descr' => 'Причины снятия с регистрационного учета: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Препараты
        'data.classificators.drugs.menu' => [
            'descr' => 'Препараты: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        // Вакцины
        'data.classificators.vaccines.menu' => [
            'descr' => 'Вакцины: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        // Оборудование
        'data.classificators.equipments.menu' => [
            'descr' => 'Оборудование: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        // Расходные материалы
        'data.classificators.exp_materials.menu' => [
            'descr' => 'Расходные материалы: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
            ],
        ],
        // Специализации
        'data.classificators.specializations.menu' => [
            'descr' => 'Специализации: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Типы ТМЦ
        'data.classificators.tmc_types.menu' => [
            'descr' => 'Типы ТМЦ: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Единицы измерения
        'data.classificators.measures.menu' => [
            'descr' => 'Единицы измерения: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
            ],
        ],
        // Прейскурант
        'data.pricelist.services.menu' => [
            'descr' => 'Прейскурант: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
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
        echo "m190305_114647_update_rbac_permissions_for_menu cannot be reverted.\n";

        return false;
    }
    */
}
