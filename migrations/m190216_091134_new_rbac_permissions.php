<?php

use app\commands\migrate\Migration;

/**
 * Class m190216_091134_new_rbac_permissions
 */
class m190216_091134_new_rbac_permissions extends Migration
{
    private static $permissionData = [
        'admin' => [
            'users' => [
                [
                    'name' => 'manage',
                    'descr' => 'Управление пользователями: поиск пользователей',
                    'descrW' => 'Управление пользователями: CUD',
                ],
                [
                    'name' => 'block',
                    'descr' => 'Управление пользователями: управление блокировкой УЗ',
                ],
                [
                    'name' => 'block-temporary',
                    'descr' => 'Управление пользователями: управление блокировкой доступов УЗ в рамках места работы (блокировка при отпуске больничном и т.д., разблокировка по выходу из отпуска, больничного и т.д.)',
                ],
                [
                    'name' => 'change-password',
                    'descr' => 'Управление пользователями: смена пароля',
                ],
                [
                    'name' => 'monitor',
                    'descr' => 'Аудит действий пользователя',
                ],
            ],
            'rbac' => [
                [
                    'name' => 'manage',
                    'descr' => 'Управление ролевой моделью: управление справочником ролей',
                ],
                [
                    'name' => 'manage-users',
                    'descr' => 'Управление ролевой моделью: поиск пользователей по ролям',
                    'descrW' => 'Управление ролевой моделью: установка/снятие роли',
                ],
            ],
        ],
        'data' => [
            'classificators' => [
                [
                    'name' => 'org_types',
                    'descr' => 'Справочник типов организаций: поиск',
                    'descrW' => 'Справочник типов организаций: CUD',
                ],
                [
                    'name' => 'measures',
                    'descr' => 'Справочник единиц измерения услуг: поиск',
                    'descrW' => 'Справочник единиц измерения услуг: CUD',
                ],
                [
                    'name' => 'reg_expire_reasons',
                    'descr' => 'Справочник причин снятия с регистрационного учета: поиск',
                    'descrW' => 'Справочник причин снятия с регистрационного учета: CUD',
                ],
                [
                    'name' => 'specializations',
                    'descr' => 'Справочник специализаций: поиск',
                    'descrW' => 'Справочник специализаций: CUD',
                ],
                [
                    'name' => 'tmc_types',
                    'descr' => 'Справочник типов ТМЦ: поиск',
                    'descrW' => 'Справочник типов ТМЦ: CUD',
                ],
                [
                    'name' => 'active_substances',
                    'descr' => 'Справочник активных веществ: поиск',
                    'descrW' => 'Справочник активных веществ: CUD',
                ],
                [
                    'name' => 'drugs',
                    'descr' => 'Справочник препаратов: поиск',
                    'descrW' => 'Справочник препаратов: CUD',
                ],
                [
                    'name' => 'vaccines',
                    'descr' => 'Справочник вакцин: поиск',
                    'descrW' => 'Справочник вакцин: CUD',
                ],
                [
                    'name' => 'equipments',
                    'descr' => 'Справочник оборудования: поиск',
                    'descrW' => 'Справочник оборудования: CUD',
                ],
                [
                    'name' => 'exp_materials',
                    'descr' => 'Справочник расходных материалов: поиск',
                    'descrW' => 'Справочник расходных материалов: CUD',
                ],
                [
                    'name' => 'species',
                    'descr' => 'Справочник видов животных: поиск',
                    'descrW' => 'Справочник видов животных: CUD',
                ],
                [
                    'name' => 'breeds',
                    'descr' => 'Справочник пород животных: поиск',
                    'descrW' => 'Справочник пород животных: CUD',
                ],
                [
                    'name' => 'diseases',
                    'descr' => 'Справочник заболеваний: поиск',
                    'descrW' => 'Справочник заболеваний: CUD',
                ],
            ],
            'pricelist' => [
                [
                    'name' => 'services',
                    'descr' => 'Справочник услуг: поиск',
                    'descrW' => 'Справочник услуг: CUD',
                ],

            ],
            'organizations' => [
                [
                    'name' => 'manage',
                    'descr' => 'Учет организаций: поиск',
                    'descrW' => 'Учет организаций: CUD',
                ],
                [
                    'name' => 'balance',
                    'descr' => 'Баланс организаций: поиск',
                    'descrW' => 'Баланс организаций: CUD',
                ],
                [
                    'name' => 'balance-tmc-disposal',
                    'descr' => 'Баланс организаций: списание ТМЦ',
                ],
            ],
            'specialists' => [
                [
                    'name' => 'manage',
                    'descr' => 'Учет специалистов: поиск',
                    'descrW' => 'Учет специалистов: CUD',
                ],
            ],
            'owners' => [
                [
                    'name' => 'manage',
                    'descr' => 'Учет владельцев животных: поиск',
                    'descrW' => 'Учет владельцев животных: CUD',
                ],
                [
                    'name' => 'notifications',
                    'descr' => 'Учет владельцев животных: настройка подписок на уведомления',
                ],
            ],
            'pets' => [
                [
                    'name' => 'manage',
                    'descr' => 'Учет животных: поиск животных',
                    'descrW' => 'Учет животных: управление животным: CUD',
                ],
                [
                    'name' => 'print',
                    'descr' => 'Учет животных: печать фрагмента карточки',
                ],
                [
                    'name' => 'vaccination-identification',
                    'descr' => 'Учет животных: предоставление данных о вакцинации, идентификации животного',
                ],
                [
                    'name' => 'reg_certificates',
                    'descr' => 'Учет животных: создание и выдача регистрационного удостоверения',
                ],
            ],
        ],
        'registry' => [
            'schedule' => [
                [
                    'name' => 'manage',
                    'descr' => 'Управление рабочим графиком: поиск по рабочему графику',
                ],
                [
                    'name' => 'shifts',
                    'descr' => 'Управление рабочим графиком: управление сменами',
                ],
                [
                    'name' => 'timesheets',
                    'descr' => 'Управление рабочим графиком: управление расписанием специалистов',
                ],
            ],
        ],
        'activity' => [
            'visits' => [
                [
                    'name' => 'manage',
                    'descr' => 'Учет приемов',
                    'descrW' => 'Учет приемов: создание приема',
                ],
                [
                    'name' => 'create',
                    'descr' => 'Учет приемов: создание приема',
                ],
                [
                    'name' => 'edit',
                    'descr' => 'Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"',
                ],
                [
                    'name' => 'services-edit',
                    'descr' => 'Управление приемом: редактирование набора услуг',
                ],
                [
                    'name' => 'confirm-payment',
                    'descr' => 'Управление приемом: управление состояние оплаты приема',
                ],
                [
                    'name' => 'start',
                    'descr' => 'Управление приемом: взятие приема в работу',
                ],
                [
                    'name' => 'tmc',
                    'descr' => 'Управление приемом:  указание данных связанных с услугой (ТМЦ, отчеты)',
                ],
                [
                    'name' => 'descriptions',
                    'descr' => 'Управление приемом: управление данными приема',
                ],
                [
                    'name' => 'finish',
                    'descr' => 'Управление приемом: завершение приема',
                ],
                [
                    'name' => 'cancel',
                    'descr' => 'Управление приемом: отмена приема в состоянии "В работе"',
                ],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        foreach (self::$permissionData as $group => $groupData) {
            foreach ($groupData as $subgroup => $subgroupData) {
                foreach ($subgroupData as $permissionData) {
                    $permissionName = $group . '.' . $subgroup . '.' . $permissionData['name'];
                    $description = $permissionData['descr'];
                    $permission = $auth->createPermission($permissionName);
                    $permission->description = $description;
                    $auth->add($permission);
                    if (isset($permissionData['descrW'])) {
                        $childPermissionName = $permissionName . '.W';
                        $childDescription = $permissionData['descrW'];
                        $childPermission = $auth->createPermission($childPermissionName);
                        $childPermission->description = $childDescription;
                        $auth->add($childPermission);
                        $auth->addChild($permission, $childPermission);
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

        foreach (self::$permissionData as $group => $groupData) {
            foreach ($groupData as $subgroup => $subgroupData) {
                foreach ($subgroupData as $permissionData) {
                    $permissionName = $group . '.' . $subgroup . '.' . $permissionData['name'];
                    if (isset($permissionData['descrW'])) {
                        $childPermissionName = $permissionName . 'W';
                        $childPermission = $auth->getPermission($childPermissionName);
                        $auth->remove($childPermission);
                    }
                    $permission = $auth->getPermission($permissionName);
                    $auth->remove($permission);
                }
            }
        }
    }
}
