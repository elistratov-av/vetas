<?php

use app\commands\migrate\Migration;

/**
 * Class m190802_122616_2049_fix_shelter_rbac
 */
class m190802_122616_2049_fix_shelter_rbac extends \app\common\migrate\RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $revoke = [
            'data.faqs.manage' => [
                'roles' => [
                    'managementShelter',
                    'specShelter',
                ],
            ],
            'data.faqs.manage.menu' => [
                'roles' => [
                    'managementShelter',
                    'specShelter',
                ],
            ],
        ];

        $assign = [
            'data.help.manage' => [
                'roles' => [
                    'managementShelter',
                    'specShelter',
                ],
            ],
            'data.help.manage.menu' => [
                'roles' => [
                    'managementShelter',
                    'specShelter',
                ],
            ],
        ];

        $this->revokePermissions($revoke);
        $this->grantPermissions($assign);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190802_122616_2049_fix_shelter_rbac cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190802_122616_2049_fix_shelter_rbac cannot be reverted.\n";

        return false;
    }
    */
}
