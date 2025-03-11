<?php

use app\common\components\rbac\Role;
use app\common\migrate\RbacMigration;

/**
 * Class m220422_092931_callcenter_rbac
 */
class m220422_092931_callcenter_rbac extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rolesToAdd = [
            [
                'role' => Role::ROLE_CALLCENTER_OPERATOR,
                'description' => 'Оператор контактного центра',
                'ext_description' => 'роль предназначена для пользователей, которые являются операторами контактного центра',
            ],
        ];

        $this->addRbacRoles($rolesToAdd);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220422_092931_callcenter_rbac cannot be reverted.\n";

        return false;
    }
}
