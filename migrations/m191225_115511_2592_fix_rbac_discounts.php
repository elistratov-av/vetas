<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m191225_115511_2592_fix_rbac_discounts
 */
class m191225_115511_2592_fix_rbac_discounts extends RbacMigration
{
    private $permissionData = [
        'data.pricelist.discount.W' => [
            'descr' => 'Справочник скидок: CUD',
            'roles' => [
                Role::ROLE_MANAGEMENT_GOS,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->permissionData);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->permissionData);
    }
}
