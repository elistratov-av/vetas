<?php

use app\commands\migrate\Migration;

/**
 * Class m190424_083638_v2_visit_tmc_split_and_fix
 */
class m190424_083638_v2_visit_tmc_split_and_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $sql = "CREATE OR REPLACE VIEW public.v2_visit_tmc_list AS
SELECT id, name, id_measure, 'drugs' AS tmc_class   FROM  drugs
UNION
SELECT id, name, id_measure, 'exp_materials' AS tmc_class FROM exp_materials
UNION
SELECT id, name, id_measure, 'vaccines' AS tmc_class FROM vaccines
";
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW public.v2_visit_tmc_list');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190424_083638_v2_visit_tmc_split_and_fix cannot be reverted.\n";

        return false;
    }
    */
}
