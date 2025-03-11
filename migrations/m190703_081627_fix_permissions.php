<?php

use app\common\migrate\RbacMigration;

/**
 * Class m190703_081627_fix_permissions
 */
class m190703_081627_fix_permissions extends RbacMigration
{
    /*
 * @see VETAIS-2048
 */
    protected $permissions_for_revoke = [
        'data.faqs.manage.menu' => [
            'roles' => [
                'dispatcher',
                'inspector',
            ]
        ]
    ];


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->permissions_for_revoke, false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /*
         * Invert
         */
        $this->revokePermissions($this->permissions_for_revoke);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190703_081627_fix_permissions cannot be reverted.\n";

        return false;
    }
    */
}
