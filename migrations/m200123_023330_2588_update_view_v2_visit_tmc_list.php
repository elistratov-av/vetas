<?php

use app\commands\migrate\Migration;

/**
 * Class m200123_023330_2588_update_view_v2_visit_tmc_list
 */
class m200123_023330_2588_update_view_v2_visit_tmc_list extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql =<<<SQL
CREATE OR REPLACE VIEW v2_visit_tmc_list AS
SELECT drugs.id,
       drugs.name,
       drugs.id_measure,
       'drugs'::text AS tmc_class,
       2::integer AS quantity_precision
FROM drugs
UNION
SELECT exp_materials.id,
       exp_materials.name,
       exp_materials.id_measure,
       'exp_materials'::text AS tmc_class,
       2::integer AS quantity_precision
FROM exp_materials
UNION
SELECT vaccines.id,
       vaccines.name,
       vaccines.id_measure,
       'vaccines'::text AS tmc_class,
       2::integer AS quantity_precision
FROM vaccines;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql =<<<SQL
CREATE OR REPLACE VIEW v2_visit_tmc_list AS
SELECT drugs.id,
       drugs.name,
       drugs.id_measure,
       'drugs'::text AS tmc_class
FROM drugs
UNION
SELECT exp_materials.id,
       exp_materials.name,
       exp_materials.id_measure,
       'exp_materials'::text AS tmc_class
FROM exp_materials
UNION
SELECT vaccines.id,
       vaccines.name,
       vaccines.id_measure,
       'vaccines'::text AS tmc_class
FROM vaccines;
SQL;

        $this->execute($sql);
    }
}
