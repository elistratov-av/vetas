<?php

use app\models\db\admin\AdminUser;

$css = <<<CSS
.treeview-menu {
    overflow-x: hidden;
}
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
                [
                    'label' => 'Настройки безопасности',
                    'icon' => 'key',
                    'url' => ['security-settings/index'],
                    'visible' => (\Yii::$app->user->can(AdminUser::ROLE_ADMIN) || \Yii::$app->user->can(AdminUser::ROLE_SECURITY)),
                ],
                [
                    'label' => 'Учетные записи',
                    'icon' => 'users',
                    'visible' => (\Yii::$app->user->can(AdminUser::ROLE_ADMIN) || \Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER) || \Yii::$app->user->can(AdminUser::ROLE_SECURITY)),
                    'items' => [
                        [
                            'label' => 'Привилегированные УЗ',
                            'icon' => 'users',
                            'url' => ['admin-users/index'],
                            'visible' => (\Yii::$app->user->can(AdminUser::ROLE_ADMIN) || \Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER) || \Yii::$app->user->can(AdminUser::ROLE_SECURITY)),
                        ],
                        [
                            'label' => 'Функциональные УЗ',
                            'icon' => 'users',
                            'url' => ['users/index'],
                            'visible' => (\Yii::$app->user->can(AdminUser::ROLE_ADMIN) || \Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER) || \Yii::$app->user->can(AdminUser::ROLE_SECURITY)),
                        ],
                    ],
                ],
                [
                    'label' => 'Пользовательские логи',
                    'icon' => 'folder-open',
                    'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                    'items' => [
                        [
                            'label' => 'Управление УЗ',
                            'icon' => 'folder-open',
                            'url' => ['log-users/general'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Управление доступами УЗ',
                            'icon' => 'folder-open',
                            'url' => ['log-users/access'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Управление блокировкой УЗ',
                            'icon' => 'folder-open',
                            'url' => ['log-users/block-manual'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Автоматическая блокировка УЗ',
                            'icon' => 'folder-open',
                            'url' => ['log-users/block-auto'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Вход/выход',
                            'icon' => 'folder-open',
                            'url' => ['log-users/authorization'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Изменение данных',
                            'icon' => 'folder-open',
                            'url' => ['log-users/audit'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                    ],
                ],
                [
                    'label' => 'Технические логи',
                    'icon' => 'folder-open',
                    'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                    'items' => [
                        [
                            'label' => 'Авторизация',
                            'icon' => 'folder-open',
                            'url' => ['log-external/authorization'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                        [
                            'label' => 'Обмен данными',
                            'icon' => 'folder-open',
                            'url' => ['log-external/data-exchange'],
                            'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                        ],
                    ],
                ],
                [
                    'label' => 'Мониторинг сессий',
                    'icon' => 'key',
                    'url' => ['sessions/index'],
                    'visible' => \Yii::$app->user->can(AdminUser::ROLE_SECURITY),
                ],
            ],
        ]
    ); ?>
</aside>
