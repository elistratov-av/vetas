<?php

use app\commands\migrate\Migration;

/**
 * Class m200123_023247_2588_update_view_v2_visit_tmc_balance
 */
class m200123_023247_2588_update_view_v2_visit_tmc_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql =<<<SQL
CREATE OR REPLACE VIEW v2_visit_tmc_balance AS
SELECT balance_drugs.id,
       balance_drugs.id_organization,
       balance_drugs.inventory_number,
       balance_drugs.price,
       'balance_drugs'::text AS balance_type,
       drugs.name,
       drugs.id_measure,
       2::integer AS quantity_precision 
FROM balance_drugs
         LEFT JOIN drugs ON balance_drugs.id_drug = drugs.id
UNION
SELECT balance_equipments.id,
       balance_equipments.id_organization,
       balance_equipments.inventory_number,
       NULL::numeric              AS price,
       'balance_equipments'::text AS balance_type,
       equipments.name,
       NULL::integer              AS id_measure,
       0::integer                 AS quantity_precision 
FROM balance_equipments
         LEFT JOIN equipments ON balance_equipments.id_equipment = equipments.id
WHERE balance_equipments.equipment_condition = 'W'::bpchar
UNION
SELECT balance_vaccines.id,
       balance_vaccines.id_organization,
       balance_vaccines.inventory_number,
       balance_vaccines.price,
       'balance_vaccines'::text AS balance_type,
       vaccines.name,
       vaccines.id_measure,
       2::integer AS quantity_precision 
FROM balance_vaccines
         LEFT JOIN vaccines ON balance_vaccines.id_vaccine = vaccines.id
UNION
SELECT balance_exp_materials.id,
       balance_exp_materials.id_organization,
       balance_exp_materials.inventory_number,
       balance_exp_materials.price,
       'balance_exp_materials'::text AS balance_type,
       exp_materials.name,
       exp_materials.id_measure,
       2::integer AS quantity_precision 
FROM balance_exp_materials
         LEFT JOIN exp_materials ON balance_exp_materials.id_exp_materials = exp_materials.id;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql =<<<SQL
CREATE OR REPLACE VIEW v2_visit_tmc_balance AS
SELECT balance_drugs.id,
       balance_drugs.id_organization,
       balance_drugs.inventory_number,
       balance_drugs.price,
       'balance_drugs'::text AS balance_type,
       drugs.name,
       drugs.id_measure
FROM balance_drugs
         LEFT JOIN drugs ON balance_drugs.id_drug = drugs.id
UNION
SELECT balance_equipments.id,
       balance_equipments.id_organization,
       balance_equipments.inventory_number,
       NULL::numeric              AS price,
       'balance_equipments'::text AS balance_type,
       equipments.name,
       NULL::integer              AS id_measure
FROM balance_equipments
         LEFT JOIN equipments ON balance_equipments.id_equipment = equipments.id
WHERE balance_equipments.equipment_condition = 'W'::bpchar
UNION
SELECT balance_vaccines.id,
       balance_vaccines.id_organization,
       balance_vaccines.inventory_number,
       balance_vaccines.price,
       'balance_vaccines'::text AS balance_type,
       vaccines.name,
       vaccines.id_measure
FROM balance_vaccines
         LEFT JOIN vaccines ON balance_vaccines.id_vaccine = vaccines.id
UNION
SELECT balance_exp_materials.id,
       balance_exp_materials.id_organization,
       balance_exp_materials.inventory_number,
       balance_exp_materials.price,
       'balance_exp_materials'::text AS balance_type,
       exp_materials.name,
       exp_materials.id_measure
FROM balance_exp_materials
         LEFT JOIN exp_materials ON balance_exp_materials.id_exp_materials = exp_materials.id;
SQL;

        $this->execute($sql);
    }
}
