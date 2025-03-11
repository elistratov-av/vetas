<aside class="main-sidebar">
<?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Учетные записи', 'icon' => 'users', 'url' => ['/admin/users']],
                    ['label' => 'Специалисты', 'icon' => 'user-md', 'url' => ['/admin/specialists']],
                    ['label' => 'Виды животных', 'icon' => 'linux', 'url' => ['/admin/species']],
                    ['label' => 'Организации', 'icon' => 'hospital-o', 'url' => ['/admin/organization']],
                    ['label' => 'Приемы', 'icon' => 'calendar-check-o', 'url' => ['/admin/visit']],
                    ['label' => 'Владельцы', 'icon' => 'smile-o', 'url' => ['/admin/owners']],
                    ['label' => 'Питомцы', 'icon' => 'paw', 'url' => ['/admin/pets']],
                    ['label' => 'Чипы старые', 'icon' => 'paw', 'url' => ['/admin/pets/chipold']],
                    ['label' => 'Чипы новые', 'icon' => 'paw', 'url' => ['/admin/pets/chips']],
                    ['label' => 'ФИАС', 'icon' => 'map-marker', 'url' => ['/admin/fias/updates']],
                    ['label' => 'Запросы изменений', 'icon' => 'edit', 'url' => ['/admin/requests']],
                    [
                        'label' => 'mos.ru',
                        'icon' => 'university',
                        'items' => [
                            ['label' => 'Услуги', 'icon' => 'book', 'url' => ['/admin/mos-ru']],
                            ['label' => 'Записи', 'icon' => 'paper-plane-o', 'url' => ['mos-ru/messages']],
                            ['label' => 'Логи', 'icon' => 'cubes', 'url' => ['mos-ru/logs']],
                        ],
                    ],
                    [
                        'label' => 'Статистика',
                        'icon' => 'bar-chart',
                        'items' => [
                            ['label' => 'mos.ru', 'icon' => 'area-chart', 'url' => ['statistics/mos-ru']],
                            ['label' => 'Нагрузка на клиники', 'icon' => 'area-chart', 'url' => ['statistics/clinics-duty-report']],
                            ['label' => 'Контроль спроса', 'icon' => 'area-chart', 'url' => ['statistics/services-report']],
                            ['label' => 'Отчет по работе сотрудников', 'icon' => 'area-chart', 'url' => ['statistics/employees-report']],
                            ['label' => 'Отчет по использованию препаратов', 'icon' => 'area-chart', 'url' => ['statistics/balance-tmc-report']],
                            ['label' => 'График пиковых часов', 'icon' => 'area-chart', 'url' => ['statistics/rush-hours-report']],
                            ['label' => 'Контроль выездной службы', 'icon' => 'area-chart', 'url' => ['statistics/ambulance-duty-report']],
                            ['label' => 'Отчет по загрузке мощностей', 'icon' => 'area-chart', 'url' => ['statistics/capacity-report']],
                            ['label' => 'Общий отчет по приемам', 'icon' => 'area-chart', 'url' => ['statistics/full-visits-report']],
                            ['label' => 'Детальный отчет по приемам', 'icon' => 'area-chart', 'url' => ['statistics/full-visits-vol2-report']],
                            ['label' => 'Первично зарегистрированные', 'icon' => 'area-chart', 'url' => ['statistics/firstly-reg-report']],
                            ['label' => 'Выданные удостоверения', 'icon' => 'area-chart', 'url' => ['statistics/reg-pets-report']],
                            ['label' => 'Снятые с учета', 'icon' => 'area-chart', 'url' => ['statistics/expire-pets-report']],
                            ['label' => 'Общий отчет', 'icon' => 'area-chart', 'url' => ['statistics/common-report']],
                        ],
                    ],
                    [
                        'label' => 'Animal-Id',
                        'icon' => 'globe',
                        'items' => [
                            ['label' => 'Конфликты', 'icon' => 'exclamation-triangle', 'url' => ['/admin/animal-id/conflicts']],
                            ['label' => 'Логи', 'icon' => 'exclamation-circle', 'url' => ['/admin/animal-id/logs']],
                            ['label' => 'Ошибки', 'icon' => 'exclamation-circle', 'url' => ['/admin/animal-id/errors']],
                            ['label' => 'Фильтры', 'icon' => 'eye', 'url' => ['/admin/animal-id/filters']],
                            ['label' => 'Конверторы', 'icon' => 'gear', 'url' => ['/admin/animal-id/converters']],
                        ],
                    ],
                    [
                        'label' => 'Андроид',
                        'icon' => 'tablet',
                        'url' => ['/admin/android'] ,
                    ],
                ]
            ]
) ?>
</aside>
