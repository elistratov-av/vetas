<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190806_084100_2212_dispatcher_rbac_fixes
 */
class m190806_084100_2212_dispatcher_rbac_fixes extends \app\common\migrate\RbacMigration
{
    private static $permission_data = [
        'data.classificators.vaccines' => [
            'roles' => [
                Role::ROLE_DISPATCHER,
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
