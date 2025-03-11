<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;

/**
 * Class m190912_081905_update_auth_item_child_pricelist_rbac
 */
class m190912_081905_update_auth_item_child_pricelist_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db->createCommand()
            ->delete(
                'auth_item_child',
                [
                    'and',
                    ['child' => 'data.pricelist.services.W'],
                    ['!=', 'parent', Role::ROLE_SYSADMIN_GOS],
                ]
            )->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190912_081905_update_auth_item_child_pricelist_rbac cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190912_081905_update_auth_item_child_pricelist_rbac cannot be reverted.\n";

        return false;
    }
    */
}
