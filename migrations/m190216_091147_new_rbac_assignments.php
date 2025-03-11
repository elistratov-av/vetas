<?php

use app\commands\migrate\Migration;

/**
 * Class m190216_091147_new_rbac_assignments
 */
class m190216_091147_new_rbac_assignments extends Migration
{
    private static $assData = [
        [
            'name' => 'admin.users.manage',
            'descr' => 'Управление пользователями: поиск пользователей',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
            ],
        ],
        [
            'name' => 'admin.users.manage.W',
            'descr' => 'Управление пользователями: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.users.block',
            'descr' => 'Управление пользователями: управление блокировкой УЗ',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.users.block-temporary',
            'descr' => 'Управление пользователями: управление блокировкой доступов УЗ в рамках места работы (блокировка при отпуске больничном и т.д., разблокировка по выходу из отпуска, больничного и т.д.)',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.users.change-password',
            'descr' => 'Управление пользователями: смена пароля',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.users.monitor',
            'descr' => 'Аудит действий пользователя',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.rbac.manage',
            'descr' => 'Управление ролевой моделью: управление справочником ролей',
            'roles' => [
                'sysAdminGos',
            ],
        ],
        [
            'name' => 'admin.rbac.manage-users',
            'descr' => 'Управление ролевой моделью: поиск пользователей по ролям',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'admin.rbac.manage-users.W',
            'descr' => 'Управление ролевой моделью: установка/снятие роли',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.classificators.org_types',
            'descr' => 'Справочник типов организаций: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.classificators.org_types.W',
            'descr' => 'Справочник типов организаций: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.classificators.measures',
            'descr' => 'Справочник единиц измерения услуг: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.measures.W',
            'descr' => 'Справочник единиц измерения услуг: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.reg_expire_reasons',
            'descr' => 'Справочник причин снятия с регистрационного учета: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        [
            'name' => 'data.classificators.reg_expire_reasons.W',
            'descr' => 'Справочник причин снятия с регистрационного учета: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.specializations',
            'descr' => 'Справочник специализаций: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.classificators.specializations.W',
            'descr' => 'Справочник специализаций: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.tmc_types',
            'descr' => 'Справочник типов ТМЦ: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.tmc_types.W',
            'descr' => 'Справочник типов ТМЦ: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.active_substances',
            'descr' => 'Справочник активных веществ: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.active_substances.W',
            'descr' => 'Справочник активных веществ: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.drugs',
            'descr' => 'Справочник препаратов: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.drugs.W',
            'descr' => 'Справочник препаратов: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.vaccines',
            'descr' => 'Справочник вакцин: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.vaccines.W',
            'descr' => 'Справочник вакцин: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.equipments',
            'descr' => 'Справочник оборудования: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.equipments.W',
            'descr' => 'Справочник оборудования: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.exp_materials',
            'descr' => 'Справочник расходных материалов: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.exp_materials.W',
            'descr' => 'Справочник расходных материалов: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.species',
            'descr' => 'Справочник видов животных: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
                'inspector',
                'trapping',
            ],
        ],
        [
            'name' => 'data.classificators.species.W',
            'descr' => 'Справочник видов животных: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.classificators.breeds',
            'descr' => 'Справочник пород животных: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
                'inspector',
                'trapping',
            ],
        ],
        [
            'name' => 'data.classificators.breeds.W',
            'descr' => 'Справочник пород животных: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.classificators.diseases',
            'descr' => 'Справочник заболеваний: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.classificators.diseases.W',
            'descr' => 'Справочник заболеваний: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.pricelist.services',
            'descr' => 'Справочник услуг: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        [
            'name' => 'data.pricelist.services.W',
            'descr' => 'Справочник услуг: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.organizations.manage',
            'descr' => 'Учет организаций: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.organizations.manage.W',
            'descr' => 'Учет организаций: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.organizations.balance',
            'descr' => 'Баланс организаций: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'data.organizations.balance.W',
            'descr' => 'Баланс организаций: CUD',
            'roles' => [
                'managementGos',
                'managementPrivFull',
            ],
        ],
        [
            'name' => 'data.organizations.balance-tmc-disposal',
            'descr' => 'Баланс организаций: списание ТМЦ',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'vetSpecGos',
            ],
        ],
        [
            'name' => 'data.specialists.manage',
            'descr' => 'Учет специалистов: поиск',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.specialists.manage.W',
            'descr' => 'Учет специалистов: CUD',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
            ],
        ],
        [
            'name' => 'data.owners.manage',
            'descr' => 'Учет владельцев животных: поиск',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.owners.manage.W',
            'descr' => 'Учет владельцев животных: CUD',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.owners.notifications',
            'descr' => 'Учет владельцев животных: настройка подписок на уведомления',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.pets.manage',
            'descr' => 'Учет животных: поиск животных',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.pets.manage.W',
            'descr' => 'Учет животных: управление животным: CUD',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.pets.print',
            'descr' => 'Учет животных: печать фрагмента карточки',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.pets.vaccination-identification',
            'descr' => 'Учет животных: предоставление данных о вакцинации, идентификации животного',
            'roles' => [
                'sysAdminGos',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'data.pets.reg_certificates',
            'descr' => 'Учет животных: создание и выдача регистрационного удостоверения',
            'roles' => [
                'vetSpecGos',
            ],
        ],
        [
            'name' => 'registry.schedule.manage',
            'descr' => 'Управление рабочим графиком: поиск по рабочему графику',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'registry.schedule.shifts',
            'descr' => 'Управление рабочим графиком: управление сменами',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
            ],
        ],
        [
            'name' => 'registry.schedule.timesheets',
            'descr' => 'Управление рабочим графиком: управление расписанием специалистов',
            'roles' => [
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
            ],
        ],
        [
            'name' => 'activity.visits.manage',
            'descr' => 'Учет приемов',
            'roles' => [
                'sysAdminGos',
                'sysAdminPrivFull',
                'managementGos',
                'managementPrivFull',
                'managementPrivMin',
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'activity.visits.create',
            'descr' => 'Учет приемов: создание приема',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'vetSpecGos',
                'vetSpecPrivFull',
                'dispatcher',
                'inspector',
                'trapping',
            ],
        ],
        [
            'name' => 'activity.visits.edit',
            'descr' => 'Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],
        [
            'name' => 'activity.visits.confirm-payment',
            'descr' => 'Управление приемом: управление состояние оплаты приема',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'activity.visits.start',
            'descr' => 'Управление приемом: взятие приема в работу',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
            ],
        ],
        [
            'name' => 'activity.visits.services-edit',
            'descr' => 'Управление приемом: редактирование набора услуг',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'activity.visits.tmc',
            'descr' => 'Управление приемом:  указание данных связанных с услугой (ТМЦ, отчеты)',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'activity.visits.descriptions',
            'descr' => 'Управление приемом: управление данными приема',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'activity.visits.finish',
            'descr' => 'Управление приемом: завершение приема',
            'roles' => [
                'vetSpecGos',
                'vetSpecPrivFull',
            ],
        ],
        [
            'name' => 'activity.visits.cancel',
            'descr' => 'Управление приемом: отмена приема в состоянии "В работе"',
            'roles' => [
                'registryGos',
                'registryPrivFull',
                'registryPrivMin',
                'vetSpecGos',
                'vetSpecPrivFull',
                'vetSpecPrivMin',
                'dispatcher',
            ],
        ],

    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$assData as $permissionData) {
            $permissionName = $permissionData['name'];
            $permission = $auth->getPermission($permissionName);
            foreach ($permissionData['roles'] as $roleName) {
                $role = $auth->getRole($roleName);
                $auth->addChild($role, $permission);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$assData as $permissionData) {
            $permissionName = $permissionData['name'];
            $permission = $auth->getPermission($permissionName);
            foreach ($permissionData['roles'] as $roleName) {
                $role = $auth->getRole($roleName);
                $auth->removeChild($role, $permission);
            }
        }
    }
}
