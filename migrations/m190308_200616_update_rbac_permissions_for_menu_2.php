<?php

use app\commands\migrate\Migration;

/**
 * Class m190308_200616_update_rbac_permissions_for_menu_2
 */
class m190308_200616_update_rbac_permissions_for_menu_2 extends Migration
{
    private static $permissionData = [
        // Поиск организаций
        'data.organizations.manage.menu' => [
            'descr' => 'Поиск организаций: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Поиск специалистов
        'data.specialists.manage.menu' => [
            'descr' => 'Поиск специалистов: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Типы организаций
        'data.classificators.org_types.menu' => [
            'descr' => 'Типы организаций: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Виды и породы животных
        'data.classificators.species-breeds.menu' => [
            'descr' => 'Виды и породы животных: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Заболевания
        'data.classificators.diseases.menu' => [
            'descr' => 'Заболевания: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Причины снятия с регистрационного учета
        'data.classificators.reg_expire_reasons.menu' => [
            'descr' => 'Причины снятия с регистрационного учета: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Специализации
        'data.classificators.specializations.menu' => [
            'descr' => 'Специализации: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Типы ТМЦ
        'data.classificators.tmc_types.menu' => [
            'descr' => 'Типы ТМЦ: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Единицы измерения
        'data.classificators.measures.menu' => [
            'descr' => 'Единицы измерения: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Прейскурант
        'data.pricelist.services.menu' => [
            'descr' => 'Прейскурант: доступность пункта меню',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        // Скидки
        'data.pricelist.discount.menu' => [
            'descr' => 'Скидки: доступность пункта меню',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'registryGos',
                'vetSpecGos',
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
        echo "m190308_200616_update_rbac_permissions_for_menu_2 cannot be reverted.\n";

        return false;
    }
    */
}
