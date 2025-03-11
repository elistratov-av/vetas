<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190806_133329_2115_inspector_rbac_fixes_3
 */
class m190806_133329_2115_inspector_rbac_fixes_3 extends \app\common\migrate\RbacMigration
{
    private static $permission_data = [
        'data.journals.manage.menu' => [
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
