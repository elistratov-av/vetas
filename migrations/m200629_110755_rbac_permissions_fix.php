<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m200629_110755_rbac_permissions_fix
 */
class m200629_110755_rbac_permissions_fix extends RbacMigration
{
    protected $permissions_for_grant = [
        // quarantine readonly
        'quarantine.manage.quarantine.R' => [
            'descr' => 'Карантины: чтение данных',
            'roles' => [
                Role::ROLE_INSPECTOR_READONLY,
                Role::ROLE_MANAGEMENT_GOS
            ]
        ],
        'quarantine.manage.menu' => [
            'descr' => 'Карантины: доступность пункта меню',
            'roles' => [
                Role::ROLE_MANAGEMENT_GOS
            ]
        ],
    ];

    protected $permissions_for_revoke = [
        'quarantine.manage.menu' => [
            'roles' => [
                Role::ROLE_SYSADMIN_GOS
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db
            ->createCommand()
            ->update('auth_item', ['type' => 2], ['name' => 'quarantine.manage.quarantine.R'])
            ->execute();

        $this->grantPermissions($this->permissions_for_grant);
        $this->revokePermissions($this->permissions_for_revoke, true);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200629_110755_rbac_permissions_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200629_110755_rbac_permissions_fix cannot be reverted.\n";

        return false;
    }
    */
}
