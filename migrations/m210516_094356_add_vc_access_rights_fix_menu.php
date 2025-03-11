<?php

use app\common\migrate\RbacMigration;
use yii\base\InvalidConfigException;

/**
 * Class m210516_094356_add_vc_access_rights_fix_menu
 */
class m210516_094356_add_vc_access_rights_fix_menu extends RbacMigration
{
    private $assign;
    private $assignOld;
    private $assignNewRole;
    private static $roles = [
        [
            'role' => 'vetSpecVaccination',
            'description' => 'Ветеринарный специалист (вак)',
            'ext_description' => 'роль предназначена для ветеринарного специалиста организации, который может заполнять журнал вакцинаций',
        ],
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->assignOld = [
            'data.vaccinationStation.R' => [
                'descr' => 'Просмотр прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                ],
            ],
            'data.vaccinationStation.W' => [
                'descr' => 'Настройка прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.vaccinationStation.menu' => [
                'descr' => 'Настройка прививочных пунктов - доступность меню',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
        ];

        $this->assignNewRole = [
            [
                'name' => 'data.classificators.org_types',
                'descr' => 'Справочник типов организаций: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.measures',
                'descr' => 'Справочник единиц измерения услуг: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.reg_expire_reasons',
                'descr' => 'Справочник причин снятия с регистрационного учета: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.tmc_types',
                'descr' => 'Справочник типов ТМЦ: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.drugs',
                'descr' => 'Справочник препаратов: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.vaccines',
                'descr' => 'Справочник вакцин: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.species',
                'descr' => 'Справочник видов животных: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.classificators.breeds',
                'descr' => 'Справочник пород животных: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.pricelist.services',
                'descr' => 'Справочник услуг: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.owners.manage',
                'descr' => 'Учет владельцев животных: поиск',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.pets.manage',
                'descr' => 'Учет животных: поиск животных',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'data.pets.vaccination-identification',
                'descr' => 'Учет животных: предоставление данных о вакцинации, идентификации животного',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.manage',
                'descr' => 'Учет приемов',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.create',
                'descr' => 'Учет приемов: создание приема',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.edit',
                'descr' => 'Управление приемом: редактирование приема в состоянии "Новый", "Изменен", "К переносу"',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.confirm-payment',
                'descr' => 'Управление приемом: управление состояние оплаты приема',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.start',
                'descr' => 'Управление приемом: взятие приема в работу',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.services-edit',
                'descr' => 'Управление приемом: редактирование набора услуг',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.tmc',
                'descr' => 'Управление приемом:  указание данных связанных с услугой (ТМЦ, отчеты)',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.descriptions',
                'descr' => 'Управление приемом: управление данными приема',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.finish',
                'descr' => 'Управление приемом: завершение приема',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
            [
                'name' => 'activity.visits.cancel',
                'descr' => 'Управление приемом: отмена приема в состоянии "В работе"',
                'roles' => [
                    'vetSpecVaccination',
                ],
            ],
        ];

        $this->assign = [
            'data.vaccinationStation.menu' => [
                'descr' => 'Доступность меню прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                ],
            ],
            'data.vaccinationStation.R' => [
                'descr' => 'Просмотр прививочных пунктов',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
            'data.vaccinationStation.W' => [
                'descr' => 'Добавление, редактирование, удаление сведений о прививочных пунктах',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.vaccinationStation.schedule.W' => [
                'descr' => 'Установление графика работы вет специалиста на прививочном пункте',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                ],
            ],
            'data.vaccinationStation.refusalToVaccinate.W' => [
                'descr' => 'Возможность проставления отметки об отказе владельца о вакцинации',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
            'data.vaccinationStation.journal.R' => [
                'descr' => 'Просмотр "Журнала регистрации и вакцинации животных" на прививочном пункте',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
            'data.vaccinationStation.journal.W' => [
                'descr' => 'Заполнение "Журнала регистрации и вакцинации животных" на прививочном пункте',
                'roles' => [
                    'sysAdminGos',
                    'managementGos',
                    'vetSpecGos',
                    'vetSpecVaccination',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->revokePermissions($this->assignOld, true);

        $auth = \Yii::$app->authManager;
        foreach (self::$roles as $roleData) {
            $role = $auth->createRole($roleData['role']);
            $role->description = $roleData['description'] . "\n" . $roleData['ext_description'];
            $auth->add($role);
        }

        $this->grantPermissions($this->assignNewRole);
        $this->grantPermissions($this->assign);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException|\yii\base\Exception
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, true);
        $this->revokePermissions($this->assignNewRole, true);

        $auth = \Yii::$app->authManager;
        foreach (self::$roles as $roleData) {
            $role = $auth->getRole($roleData['role']);
            $auth->remove($role);
        }

        $this->grantPermissions($this->assignOld);
    }
}
