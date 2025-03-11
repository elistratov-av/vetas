<?php

use app\common\components\rbac\Role;

$css = <<<CSS
.treeview-menu {
    overflow-x: hidden;
}

.main-sidebar {
    width: 320px;
}

@media (min-width: 768px)
{.sidebar-mini.sidebar-collapse .main-sidebar {
    -webkit-transform: translate(0, 0);
    -ms-transform: translate(0, 0);
    -o-transform: translate(0, 0);
    transform: translate(0, 0);
    width: 40px !important;
    z-index: 850;
    overflow: hidden;
    }}

CSS;

$this->registerCss($css);
?>
<aside class="main-sidebar">
    <?php echo dmstr\widgets\Menu::widget(
        [
            'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
            'labelTemplate' => '{label}',
            'linkTemplate' => '<a href="{url}" title="{label}">{icon} {label}</a>',
            'items' => [
                // [
                //     'label' => 'Учетные записи',
                //     'icon' => 'users',
                //     'url' => ['/adminv/users/index'],
                //     'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)),
                // ],
                // [
                //     'label' => 'Ролевая модель',
                //     'icon' => 'key',
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //     'items' => [
                //         ['label' => 'Роли', 'icon' => 'list-ul', 'url' => ['/adminv/rbac/roles']],
                //         ['label' => 'Разрешения', 'icon' => 'list-ol', 'url' => ['/adminv/rbac/permissions']],
                //     ],
                // ],
                // [
                //     'label' => 'Организации',
                //     'icon' => 'hospital-o',
                //     'url' => ['/adminv/organizations/index'],
                //     'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)),
                // ],
                // [
                //     'label' => 'Приюты',
                //     'icon' => 'hospital-o',
                //     'url' => ['/adminv/shelters/index'],
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                // ],
                // [
                //     'label' => 'Сотрудники приюта',
                //     'icon' => 'users',
                //     'url' => ['/adminv/shelters-users/index'],
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT),
                // ],
                // [
                //     'label' => 'ФИАС',
                //     'icon' => 'map-marker',
                //     'url' => ['/adminv/fias/updates'],
                //     //https://jira.altarix.ru/browse/VETAIS-3406
                //     //'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //     'visible' => false
                // ],
                // [
                //     'label' => 'Запросы изменений',
                //     'icon' => 'edit',
                //     'url' => ['/adminv/requests/index'],
                //     'visible' => \Yii::$app->user->can('data.change_requests.manage'),
                // ],
                // [
                //     'label' => 'Уведомления',
                //     'icon' => 'envelope',
                //     'url' => ['/adminv/notifications/log'],
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                // ],
                // [
                //     'label' => 'Аудит',
                //     'icon' => 'folder-open',
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //     'items' => [
                //         /*
                //          * MosRu
                //          */
                //         [
                //             'label' => 'mos.ru',
                //             'icon' => 'university',
                //             'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //             'items' => [
                //                 ['label' => 'Услуги', 'icon' => 'book', 'url' => ['/adminv/mos-ru/index']],
                //                 ['label' => 'Записи', 'icon' => 'paper-plane-o', 'url' => ['/adminv/mos-ru/messages']],
                //                 ['label' => 'Логи', 'icon' => 'cubes', 'url' => ['/adminv/mos-ru/logs']],
                //             ],
                //         ],
                //         /*
                //          * Found-pets
                //          */
                //         [
                //             'label' => 'Поиск животных',
                //             'icon' => 'search',
                //             'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //             'items' => [
                //                 ['label' => 'Заявки', 'icon' => 'paper-plane-o', 'url' => ['/adminv/found-pet/messages']],
                //                 ['label' => 'Отправленные статусы', 'icon' => 'paper-plane-o', 'url' => ['/adminv/found-pet/messages-sent']],
                //                 ['label' => 'Объявления', 'icon' => 'book', 'url' => ['/adminv/found-pet/ads']],
                //             ],
                //         ],
                //         /*
                //          * Аудит
                //          */
                //         ['label' => 'Питомцы', 'icon' => 'paw', 'url' => ['/adminv/audit/pets/index']],
                //         ['label' => 'Организации', 'icon' => 'hospital-o', 'url' => ['/adminv/audit/organizations/index']],
                //         ['label' => 'Владельцы', 'icon' => 'smile-o', 'url' => ['/adminv/audit/pet-owners/index']],
                //         ['label' => 'Приемы', 'icon' => 'calendar-check-o', 'url' => ['/adminv/visit/index']],
                //         ['label' => 'Удаления расписаний', 'icon' => 'calendar-o', 'url' => ['/adminv/audit/audit/timesheet']],
                //     ],
                // ],
                [
                    'label' => 'Отчеты',
                    'icon' => 'bar-chart',
                    'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)
                        || \Yii::$app->user->can(Role::ROLE_FOUND_PET_MODERATOR)),
                    'items' => [
                        [
                            'label' => 'Общий отчет по услугам',
                            'icon' => 'area-chart',
                            'url' => ['statistics/full-services-report']
                        ],
                        [
                            'label' => 'Детальный отчет по услугам',
                            'icon' => 'area-chart',
                            'url' => ['statistics/full-services-vol2-report']
                        ],
                        [
                            'label' => 'Поиск животных',
                            'icon' => 'area-chart',
                            'url' => ['statistics/search-pets-report']
                        ],
                        [
                            'label' => 'Владельцы с эл. почтой',
                            'icon' => 'area-chart',
                            'url' => ['statistics/email-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Невакцинированные животные',
                            'icon' => 'area-chart',
                            'url' => ['statistics/unvacc-pets-report', 'bti_city_area_code' => [403,404,405,414,406,407,408,409,411,412,410,413,416,417,418,415]],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'mos.ru',
                            'icon' => 'area-chart',
                            'url' => ['statistics/mos-ru'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Нагрузка на клиники',
                            'icon' => 'area-chart',
                            'url' => ['statistics/clinics-duty-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Контроль спроса',
                            'icon' => 'area-chart',
                            'url' => ['statistics/services-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Работа сотрудников',
                            'icon' => 'area-chart',
                            'url' => ['statistics/employees-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Оказанные вет. услуги',
                            'icon' => 'area-chart',
                            'url' => ['statistics/vet-services-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Использование вакцин',
                            'icon' => 'area-chart',
                            'url' => ['statistics/balance-vacc-report'],
                        ],
                        [
                            'label' => 'Расходные материалы',
                            'icon' => 'area-chart',
                            'url' => ['statistics/balance-exp-report'],
                        ],
                        [
                            'label' => 'Использование препаратов',
                            'icon' => 'area-chart',
                            'url' => ['statistics/balance-tmc-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'График пиковых часов',
                            'icon' => 'area-chart',
                            'url' => ['statistics/rush-hours-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Работа ВПД',
                            'icon' => 'area-chart',
                            'url' => ['statistics/ambulance-duty-report'],
                            'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                        ],
                        [
                            'label' => 'Загрузка мощностей',
                            'icon' => 'area-chart',
                            'url' => ['statistics/capacity-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Общий отчет по приемам',
                            'icon' => 'area-chart',
                            'url' => ['statistics/full-visits-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Детальный отчет по приемам',
                            'icon' => 'area-chart',
                            'url' => ['statistics/full-visits-vol2-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Первично зарегистрированные',
                            'icon' => 'area-chart',
                            'url' => ['statistics/firstly-reg-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Выданные удостоверения',
                            'icon' => 'area-chart',
                            'url' => ['statistics/reg-pets-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Снятые с учета',
                            'icon' => 'area-chart',
                            'url' => ['statistics/expire-pets-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Охват вакцинации',
                            'icon' => 'area-chart',
                            'url' => ['/adminv/statistics/vaccination-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Общий отчет',
                            'icon' => 'area-chart',
                            'url' => ['statistics/common-report'],
                            'visible' => (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) || \Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS))
                        ],
                        [
                            'label' => 'Уведомления',
                            'icon' => 'area-chart',
                            'url' => ['statistics/notifications-report'],
                        ],
                        [
                            'label' => 'События поиска',
                            'icon' => 'area-chart',
                            'url' => ['statistics/search-events-report'],
                        ],
                    ],
                ],
                // [
                //     'label' => 'Animal-Id',
                //     'icon' => 'globe',
                //     'visible' => \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS),
                //     'items' => [
                //         ['label' => 'Конфликты', 'icon' => 'exclamation-triangle', 'url' => ['/adminv/animal-id/conflicts']],
                //         ['label' => 'Логи', 'icon' => 'exclamation-circle', 'url' => ['/adminv/animal-id/logs']],
                //         ['label' => 'Ошибки', 'icon' => 'exclamation-circle', 'url' => ['/adminv/animal-id/errors']],
                //         ['label' => 'Фильтры', 'icon' => 'eye', 'url' => ['/adminv/animal-id/filters']],
                //         ['label' => 'Конверторы', 'icon' => 'gear', 'url' => ['/adminv/animal-id/converters']],
                //     ],
                // ],
            ],
        ]
    ); ?>
</aside>
