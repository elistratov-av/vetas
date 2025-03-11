<?php

use app\commands\migrate\Migration;

/**
 * Class m190211_145911_view_v2_visit_tmc_list
 */
class m190211_145911_view_v2_visit_tmc_list extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "CREATE OR REPLACE VIEW public.v2_visit_tmc_list AS
SELECT id, name, id_tmc_type, id_measure  FROM  drugs
UNION
SELECT id, name, id_tmc_type, NULL AS id_measure FROM equipments
UNION
SELECT id, name, id_tmc_type, id_measure FROM exp_materials
UNION
SELECT id, name, id_tmc_type, id_measure FROM vaccines
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
        echo "m190211_145911_view_v2_visit_tmc_list cannot be reverted.\n";

        return false;
    }
    */
}
