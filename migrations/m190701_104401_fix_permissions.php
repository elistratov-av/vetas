<?php

use app\common\migrate\RbacMigration;
/**
 * Class m190701_104401_fix_permissions
 */
class m190701_104401_fix_permissions extends RbacMigration
{

    /*
     * @see VETAIS-2048
     */
    protected $permissions_for_grant = [
        // FAQ (Справочная информация)
        'data.faqs.manage' => [
        'roles' => [
                'vetSpecGosAmb',
                'dispatcher',
                'inspector',
            ]
        ],

        /*
         * @see VETAIS-2060
         */
        // АПН
        'data.gosvetnadzor.violation_admin_rights.W' => [
            'roles' => [
                'managementGos',
            ]
        ],
    ];

    /*
     * @see VETAIS-2048
     */
    protected $permissions_for_revoke = [
        'data.descriptions-templates.manage' => [
            'roles' => [
                'vetSpecGosAmb',
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->permissions_for_grant);
        $this->revokePermissions($this->permissions_for_revoke, false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /*
         * Invert
         */
        $this->grantPermissions($this->permissions_for_revoke);
        $this->revokePermissions($this->permissions_for_grant, false);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190701_104401_fix_permissions cannot be reverted.\n";

        return false;
    }
    */
}
