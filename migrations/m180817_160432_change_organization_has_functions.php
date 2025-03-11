<?php

use yii\db\Migration;

/**
 * Class m180817_160432_change_organization_has_functions
 */
class m180817_160432_change_organization_has_functions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_on_balance(
    tmc_class text,
    organization_id integer,
    tmc_type_id integer)
  RETURNS boolean AS
\$BODY$
begin
    CASE 
        WHEN tmc_class = 'drug' THEN RETURN organization_has_drugs(organization_id, tmc_type_id);
        WHEN tmc_class = 'equipment' THEN RETURN organization_has_equipments(organization_id, tmc_type_id);
        WHEN tmc_class = 'vaccine' THEN RETURN organization_has_vaccine(organization_id, tmc_type_id);
    END CASE;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute("DROP FUNCTION public.organization_on_balance(text, integer, integer)");
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_on_balance(text, integer, integer) IS 'Функциля для определения наличия на балансе организации определенного ТМЦ по его типу'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_drugs(
    integer,
    integer)
  RETURNS boolean AS
\$BODY\$SELECT EXISTS (
    SELECT 1 FROM balance_drugs 
    JOIN drugs ON drugs.id = balance_drugs.id_drug
    WHERE balance_drugs.id_organization = $1 AND drugs.id_tmc_type = $2 AND balance_drugs.dose_count > 0
);\$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_drugs(integer, integer) IS 'Функциля для определения наличия на балансе организации препарата'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_equipments(
    integer,
    integer)
  RETURNS boolean AS
\$BODY\$SELECT EXISTS (
    SELECT 1 FROM balance_equipments 
    JOIN equipments ON equipments.id = balance_equipments.id_equipment
    WHERE balance_equipments.id_organization = $1 AND equipments.id_tmc_type = $2
);\$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_equipments(integer, integer) IS 'Функциля для определения наличия на балансе организации определенного оборудования'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(
    integer,
    integer)
  RETURNS boolean AS
\$BODY\$SELECT EXISTS (
    SELECT 1 FROM balance_vaccines 
    JOIN vaccines ON vaccines.id = balance_vaccines.id_vaccine
    WHERE balance_vaccines.id_organization = $1 AND vaccines.id_tmc_type = $2 AND balance_vaccines.dose_count > 0
);\$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer, integer) IS 'Функциля для определения наличия на балансе организации определенной вакцины';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_on_balance(
    tmc_class text,
    organization_id integer,
    tmc_id integer)
  RETURNS boolean AS
\$BODY$
begin
    CASE 
        WHEN tmc_class = 'equipment' THEN RETURN organization_has_equipments(organization_id, tmc_id);
        WHEN tmc_class = 'drug' THEN RETURN organization_has_drugs(organization_id, tmc_id);
        WHEN tmc_class = 'vaccine' THEN RETURN organization_has_vaccine(organization_id, tmc_id);
    END CASE;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute("DROP FUNCTION public.organization_on_balance(text, integer, integer)");
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_on_balance(text, integer, integer) IS 'Функциля для определения наличия на балансе организации определенного ТМЦ по его типу'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_drugs(
    integer,
    integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_drugs WHERE id_organization = $1 AND id_drug = $2 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_drugs(integer, integer) IS 'Функциля для определения наличия на балансе организации препарата'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_equipments(
    integer,
    integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_equipments WHERE id_organization = $1 AND id_equipment = $2);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_equipments(integer, integer) IS 'Функциля для определения наличия на балансе организации определенного оборудования'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(
    integer,
    integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_vaccines WHERE id_organization = $1 AND id_vaccine = $2 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer, integer) IS 'Функциля для определения наличия на балансе организации определенной вакцины'");
    }
}
