<?php

use app\commands\migrate\Migration;

/**
 * Class m210321_184356_3296_add_col_count_in_production_form_in_tmc_balance
 */
class m210321_184356_3296_add_col_count_in_production_form_in_tmc_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'tmc.balance',
            'count_in_production_form',
            $this->decimal(11, 2)
        );
        $sql = "
UPDATE
tmc.balance SET count_in_production_form = tmc.balance.count / tmc.production_form.volume
FROM tmc.production_form
WHERE tmc.production_form.id = tmc.balance.id_production_form";

        $this->execute($sql);

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

        $this->addCommentOnColumn(
            'tmc.balance', 'count', 'Кол-во в единицах измерения (значимый параметр)'
        );
        $this->addCommentOnColumn(
            'tmc.balance', 'count_in_production_form', 'Кол-во в формах производства (справочный параметр)'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE tmc.balance DROP CONSTRAINT count_in_production_form_check;');
        $this->dropColumn(
            'tmc.balance',
            'count_in_production_form'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210321_184356_3296_add_col_count_in_production_form_in_tmc_balance cannot be reverted.\n";

        return false;
    }
    */
}
