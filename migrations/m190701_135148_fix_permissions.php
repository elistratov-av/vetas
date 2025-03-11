<?php

use app\common\migrate\RbacMigration;

/**
 * Class m190701_135148_fix_permissions
 */
class m190701_135148_fix_permissions extends RbacMigration
{

    /*
     * @see VETAIS-2048
     */
    protected $permissions_for_revoke = [
        'activity.visits.descriptions-templates' => [
            'roles' => [
                'vetSpecGosAmb',
            ]
        ],
        'data.descriptions-templates.manage.menu' => [
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
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190701_135148_fix_permissions cannot be reverted.\n";

        return false;
    }
    */
}
