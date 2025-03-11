<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_161512_update_rbac_permissions_menu_balance
 */
class m190311_161512_update_rbac_permissions_menu_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('delete from "auth_item_child" where "parent"=\'registryGos\' and "child"=\'data.organizations.balance.menu\'');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190311_161512_update_rbac_permissions_menu_balance cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190311_161512_update_rbac_permissions_menu_balance cannot be reverted.\n";

        return false;
    }
    */
}
