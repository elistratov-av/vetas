<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m200630_103755_recreate_qua_menu_permission
 */
class m200630_103755_recreate_qua_menu_permission extends RbacMigration
{
    protected $permissions_for_grant = [
        'quarantine.manage.menu' => [
            'descr' => 'Карантины: доступность пункта меню',
            'roles' => [
                Role::ROLE_MANAGEMENT_GOS,
                Role::ROLE_INSPECTOR_READONLY,
                Role::ROLE_INSPECTOR
            ]
        ],
        'quarantine.manage.animals.R' => [
            'descr' => 'Карантины: чтение списка животных для вакцинации',
            'roles' => [
                Role::ROLE_MANAGEMENT_GOS,
                Role::ROLE_INSPECTOR_READONLY
            ]
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->permissions_for_grant);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->permissions_for_grant, false);

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200630_103755_recreate_qua_menu_permission cannot be reverted.\n";

        return false;
    }
    */
}
