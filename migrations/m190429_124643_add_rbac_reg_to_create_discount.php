<?php

use app\commands\migrate\Migration;

/**
 * Class m190429_104643_add_rbac_reg_to_create_discount
 */
class m190429_124643_add_rbac_reg_to_create_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $items =
            [
                ['managementGos', 'data.pricelist.services.W'],
                ['managementPrivMin', 'data.pricelist.services.W'],
                ['managementPrivFull', 'data.pricelist.services.W'],
                ['registryGos', 'data.pricelist.services.W'],
                ['registryPrivFull', 'data.pricelist.services.W'],
                ['registryPrivMin', 'data.pricelist.services.W'],
                ['sysAdminGos', 'data.pricelist.services.W'],
                ['sysAdminPrivFull', 'data.pricelist.services.W'],
            ];

        foreach ($items as $item) {
            $this->upsert('auth_item_child', ['parent' => $item[0], 'child' => $item[1]], false);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190429_104643_add_rbac_reg_to_create_discount cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190429_104643_add_rbac_reg_to_create_discount cannot be reverted.\n";

        return false;
    }
    */
}
