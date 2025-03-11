<?php

use app\commands\migrate\Migration;

/**
 * Class m210324_061222_3296_tmc_balance_fix
 */
class m210324_061222_3296_tmc_balance_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Мы их не сохраняем
        $this->execute("ALTER TABLE tmc.balance  DROP CONSTRAINT count_check;");
        $this->execute("ALTER TABLE tmc.balance  DROP CONSTRAINT count_in_production_form_check;");

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT count_check 
    CHECK (
        (type_tmc = 'equipment'::tmc.tmc_class_list AND count IS NULL)
            OR 
        type_tmc <> 'equipment'::tmc.tmc_class_list
        )");

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT count_in_production_form_check 
    CHECK (
        (type_tmc = 'equipment'::tmc.tmc_class_list AND count_in_production_form IS NULL)
            OR 
        type_tmc <> 'equipment'::tmc.tmc_class_list
       
        )");

        $this->execute("
ALTER TABLE tmc.balance_flow
  ADD CONSTRAINT count_check 
    CHECK (count IS NOT NULL AND count > 0)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->execute("ALTER TABLE tmc.balance  DROP CONSTRAINT count_check;");
        $this->execute("ALTER TABLE tmc.balance  DROP CONSTRAINT count_in_production_form_check;");
        $this->execute("ALTER TABLE tmc.balance_flow  DROP CONSTRAINT count_check;");
        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT count_check 
    CHECK (
        type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
            OR 
            (
            type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
                AND  
            count IS NOT NULL
            )
        )");

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT count_in_production_form_check 
    CHECK (
        (
            type_tmc 
             NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list) 
                AND count_in_production_form IS NULL
        )
            OR 
        (
            type_tmc 
             IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list) 
             AND count_in_production_form IS NOT NULL
            )
        )");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210324_061222_3296_tmc_balance_fix cannot be reverted.\n";

        return false;
    }
    */
}
