<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190805_032055_2233_change_requests_rbac
 */
class m190805_032055_2233_change_requests_rbac extends \app\common\migrate\RbacMigration
{
    private static $permission_data = [
        'data.change_requests.create' => [
            'descr' => 'Запрос изменения: создание запроса',
            'roles' => [
                Role::ROLE_REGISTRY_GOS,
                Role::ROLE_VET_SPECIALIST_GOS,
                Role::ROLE_DISPATCHER,
            ],
        ],
        'data.change_requests.manage' => [
            'descr' => 'Запрос изменения: поиск запросов',
            'roles' => [
                Role::ROLE_SYSADMIN_GOS,
            ],
        ],
        'data.change_requests.process' => [
            'descr' => 'Запрос изменения: обработка запросов (отклонение, принятие)',
            'roles' => [
                Role::ROLE_SYSADMIN_GOS,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions(self::$permission_data);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions(self::$permission_data);
    }
}
