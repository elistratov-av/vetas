<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m191225_115454_2591_fix_rbac_mosru_services
 */
class m191225_115454_2591_fix_rbac_mosru_services extends RbacMigration
{
    private $permissionData = [
        'data.pricelist.mos-ru-services.W' => [
            'descr' => 'Услуги mos.ru которые специалист может оказать в клинике: CUD',
            'rule_name' => 'UserAllOrgsRule',
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
