<?php

use yii\db\Migration;

/**
 * Class m180704_120050_add_unique_key_to_balance_equipments
 */
class m180704_120050_add_unique_key_to_balance_equipments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE balance_equipments DROP CONSTRAINT balance_equipments_inventory_number_key");
        $this->execute("ALTER TABLE balance_equipments ADD CONSTRAINT 
            balance_equipments_inventory_number_organization_key UNIQUE (id_organization, inventory_number)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE balance_equipments DROP CONSTRAINT balance_equipments_inventory_number_organization_key");
        $this->execute("ALTER TABLE balance_equipments ADD CONSTRAINT 
            balance_equipments_inventory_number_key UNIQUE (inventory_number)");
    }
}
