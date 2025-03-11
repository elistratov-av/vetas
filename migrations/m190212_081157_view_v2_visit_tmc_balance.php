<?php

use app\commands\migrate\Migration;

/**
 * Class m190212_081157_view_v2_visit_tmc_balance
 */
class m190212_081157_view_v2_visit_tmc_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "CREATE OR REPLACE VIEW public.v2_visit_tmc_balance AS
SELECT
  balance_drugs.id,
  id_organization,
  inventory_number,
  price,
  'balance_drugs'  AS balance_type,
  drugs.name       AS name,
  drugs.id_measure AS id_measure
FROM balance_drugs
       LEFT JOIN drugs ON balance_drugs.id_drug = drugs.id
UNION
SELECT
  balance_equipments.id,
  id_organization,
  inventory_number,
  NULL                 AS price,
  'balance_equipments' AS balance_type,
  equipments.name      AS name,
  NULL                 AS id_measure
FROM balance_equipments
       LEFT JOIN equipments on balance_equipments.id_equipment = equipments.id
UNION
SELECT
  balance_vaccines.id,
  id_organization,
  inventory_number,
  price,
  'balance_vaccines'  AS balance_type,
  vaccines.name       AS name,
  vaccines.id_measure AS id_measure
FROM balance_vaccines
       LEFT JOIN vaccines ON balance_vaccines.id_vaccine = vaccines.id
UNION
SELECT
  balance_exp_materials.id,
  id_organization,
  inventory_number,
  price,
  'balance_exp_materials'  AS balance_type,
  exp_materials.name       AS name,
  exp_materials.id_measure AS id_measure
FROM balance_exp_materials
       LEFT JOIN exp_materials ON balance_exp_materials.id_exp_materials = exp_materials.id
;";
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW public.v2_visit_tmc_balance');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190212_081157_view_v2_visit_tmc_balance cannot be reverted.\n";

        return false;
    }
    */
}
