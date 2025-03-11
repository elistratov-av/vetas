<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190806_082213_2115_inspector_rbac_fixes_2
 */
class m190806_082213_2115_inspector_rbac_fixes_2 extends \app\common\migrate\RbacMigration
{
    private static $to_revoke = [
        'data.specialists.manage.menu' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.specialists.manage' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
    ];

    private static $to_grant = [
        'data.pets.manage.menu' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
        'data.pets.manage' => [
            'roles' => [
                Role::ROLE_INSPECTOR,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->revokePermissions(self::$to_revoke);
        $this->grantPermissions(self::$to_grant);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions(self::$to_grant);
        $this->grantPermissions(self::$to_revoke);
    }
}
