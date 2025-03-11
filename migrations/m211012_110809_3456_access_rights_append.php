<?php

use app\common\migrate\RbacMigration;

/**
 * Class m211012_110809_3456_access_rights_append
 */
class m211012_110809_3456_access_rights_append extends RbacMigration
{
    protected $assign = [
        'data.owners.manage.W' => [
            'roles' => [
                'sysAdminGos',
                'managementGos',
            ],
        ],
        'data.pets.manage.W' => [
            'roles' => [
                'sysAdminGos',
                'managementGos',
            ],
        ],
        'data.owners.manage.W' => [
            'roles' => [
                'sysAdminGos',
                'managementGos',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->grantPermissions($this->assign);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->revokePermissions($this->assign, false);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211012_110809_3456_access_rights_append cannot be reverted.\n";

        return false;
    }
    */
}
