<?php

use app\commands\migrate\Migration;

/**
 * Class m190212_102016_fix_inventory_number_in_balances
 */
class m190212_102016_fix_inventory_number_in_balances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // balance_drugs
        $this->execute("ALTER TABLE balance_drugs DROP CONSTRAINT balance_drugs_inventory_number_key");
        $this->execute("ALTER TABLE balance_drugs ADD CONSTRAINT 
            balance_drugs_inventory_number_organization_key UNIQUE (id_organization, inventory_number)");

        // balance_exp_materials
        $this->execute("ALTER TABLE balance_exp_materials ADD CONSTRAINT 
            balance_exp_materials_inventory_number_organization_key UNIQUE (id_organization, inventory_number)");

        // balance_vaccines
        $this->execute("ALTER TABLE balance_vaccines ADD CONSTRAINT 
            balance_vaccines_inventory_number_organization_key UNIQUE (id_organization, inventory_number)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // balance_drugs
        $this->execute("ALTER TABLE balance_drugs DROP CONSTRAINT balance_drugs_inventory_number_organization_key");
        $this->execute("ALTER TABLE balance_drugs ADD CONSTRAINT 
            balance_drugs_inventory_number_key UNIQUE (inventory_number)");

        // balance_exp_materials
        $this->execute("ALTER TABLE balance_exp_materials DROP CONSTRAINT balance_exp_materials_inventory_number_organization_key");

        // balance_vaccines
        $this->execute("ALTER TABLE balance_vaccines DROP CONSTRAINT balance_vaccines_inventory_number_organization_key");

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190212_102016_fix_inventory_number_in_balances cannot be reverted.\n";

        return false;
    }
    */
}
