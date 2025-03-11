<?php

use app\common\migrate\RbacMigration;


/**
 * Class m190628_123002_fix_permissions
 */
class m190628_123002_fix_permissions extends RbacMigration
{
    /*
     * @see VETAIS-2048
     */
    protected $permissions_for_grant = [
        // Управление организацией, баланс организации
        'data.organizations.balance.menu' => [
            'roles' => [
                'registryGos',
                'vetSpecGos',
            ]
        ],
        // Журналы
        'data.journals.manage.menu' => [
            'roles' => [
                'vetSpecGosAmb',
                'dispatcher',
            ]
        ],
        'data.journals.manage' => [
            'roles' => [
                'vetSpecGosAmb',
                'dispatcher',
            ]
        ],
        // заболевания
        'data.classificators.diseases.menu' => [
            'roles' => [
                'inspector',
                'dispatcher',
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->permissions_for_grant);
        //$this->revokePermissions($this->permissions_for_revoke, false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /*
         * Invert
         */
        //$this->grantPermissions($this->permissions_for_revoke);
        $this->revokePermissions($this->permissions_for_grant, false);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190628_123002_fix_permissions cannot be reverted.\n";

        return false;
    }
    */
}
