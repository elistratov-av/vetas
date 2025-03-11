<?php

use app\commands\migrate\Migration;

/**
 * Class m190430_071648_clear_old_balances
 */
class m190430_071648_clear_old_balances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
UPDATE balance_drugs SET dose_count = 0
WHERE id NOT IN (
  SELECT id_balance_tmc_type FROM balance_flow WHERE balance_tmc_type = 'drug')
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE balance_exp_materials SET count = 0
WHERE id NOT IN (
  SELECT id_balance_tmc_type FROM balance_flow WHERE balance_tmc_type = 'exp_material')
SQL;
        $this->execute($sql);



        $sql = <<<SQL
UPDATE balance_vaccines SET dose_count = 0
WHERE id NOT IN (
  SELECT id_balance_tmc_type FROM balance_flow WHERE balance_tmc_type = 'vaccine')
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190430_071648_clear_old_balances cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190430_071648_clear_old_balances cannot be reverted.\n";

        return false;
    }
    */
}
