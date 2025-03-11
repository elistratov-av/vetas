<?php

use yii\db\Migration;

/**
 * Class m180726_121631_add_organization_services_functions
 */
class m180726_121631_add_organization_services_functions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW public.organizations_services;");
        $this->execute("DROP VIEW public.real_organization_specializations;");
        $this->execute("DROP FUNCTION IF EXISTS public.service_reason(boolean, boolean, boolean, text, text, text)");
        $this->execute("DROP FUNCTION IF EXISTS public.service_sign(boolean, boolean, boolean)");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_drugs(integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_drugs WHERE id_organization = $1 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_drugs(integer) IS 'Функциля для определения наличия на балансе организации любых препаратов';");

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
        $this->execute("COMMENT ON FUNCTION public.organization_has_drugs(integer, integer) IS 'Функциля для определения наличия на балансе организации препарата';");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_equipments(integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_equipments WHERE id_organization = $1);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_equipments(integer) IS 'Функциля для определения наличия на балансе организации любого оборудования'");

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
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_drugs WHERE id_organization = $1 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer) IS 'Функциля для определения наличия на балансе организации любых вакцин'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(
    integer,
    integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_drugs WHERE id_organization = $1 AND id_drug = $2 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer, integer) IS 'Функциля для определения наличия на балансе организации определенной вакцины'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_on_balance(
    tmc_class text,
    organization_id integer)
  RETURNS boolean AS
\$BODY$
begin
    CASE 
        WHEN tmc_class = 'equipment' THEN RETURN organization_has_equipments(organization_id);
        WHEN tmc_class = 'drug' THEN RETURN organization_has_drugs(organization_id);
        WHEN tmc_class = 'vaccine' THEN RETURN organization_has_vaccine(organization_id);
        ELSE RETURN false;
    END CASE;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_on_balance(text, integer) IS 'Функциля для определения наличия на балансе организации ТМЦ по его типу'");

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
        WHEN tmc_class = 'grug' THEN RETURN organization_has_drugs(organization_id, tmc_id);
        WHEN tmc_class = 'vaccine' THEN RETURN organization_has_vaccine(organization_id, tmc_id);
    END CASE;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_on_balance(text, integer, integer) IS 'Функциля для определения наличия на балансе организации определенного ТМЦ по его типу'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_balance_for_service(
    IN organization_id integer,
    IN service_id integer)
  RETURNS TABLE(id_tmc integer, service_tmc_class character varying, name character varying, tmc_class tmc_class_list, available boolean) AS
\$BODY$
begin
    return query
        select 
            service_tmcs.id_tmc, service_tmcs.tmc_class, tmc.name, tmc_types.tmc_class,
            case 
                when tmc.id is not null then organization_on_balance(tmc_types.tmc_class::text, $1, tmc.id)
                else organization_on_balance(service_tmcs.tmc_class, $1)
            end
        from service_tmcs
        left join tmc on tmc.id = service_tmcs.id_tmc
        left join tmc_types on tmc_types.id = tmc.id_tmc_type
        where service_tmcs.id_service = $2 AND service_tmcs.is_required;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_balance_for_service(integer, integer) IS 'Функциля для вывода ТМЦ необходимых для услуги, с определением их наличия у организации'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.service_sign(
    cabinet_exists boolean,
    specialist_exists boolean,
    organization_id integer,
    service_id integer)
  RETURNS boolean AS
\$BODY$
begin
    return cabinet_exists AND specialist_exists AND (EXISTS (SELECT 1 from public.organization_balance_for_service(organization_id, service_id) where available));
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.service_sign(boolean, boolean, integer, integer) IS 'Функциля для определения доступности услуги в организации'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.service_reason(
    cabinet_exists boolean,
    specialist_exists boolean,
    cabinet_name text,
    specialist_name text,
    organization_id integer,
    service_id integer)
  RETURNS text AS
\$BODY$
declare
    result text;
    rows record;
    name text;
    names text[];
begin
    CASE 
        WHEN NOT cabinet_exists THEN result := 'Нет кабинета ' || cabinet_name;
        WHEN NOT specialist_exists THEN result := 'Нет специалиста ' || specialist_name;
        WHEN EXISTS (SELECT 1 from public.organization_balance_for_service(organization_id, service_id) where NOT available) THEN            
            FOR rows IN SELECT * FROM public.organization_balance_for_service(organization_id, service_id) where NOT available LOOP
                CASE 
                    WHEN rows.service_tmc_class='equipment' THEN name := 'оборудования';
                    WHEN rows.service_tmc_class='drug' THEN name := 'препаратов';
                    WHEN rows.service_tmc_class='vaccine' THEN name := 'вакцин';
                    ELSE name := '';
                END CASE;

                names := array_append(names, COALESCE(rows.name, name, '')::text);
            END LOOP;
            result := 'Нет на балансе: ' || array_to_string(names, ', ');
        ELSE result := '';
    END CASE;

    RETURN result;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.service_reason(boolean, boolean, text, text, integer, integer) IS 'Функциля для определения причины недоступности услуги в организации'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.real_organization_specializations AS 
 SELECT DISTINCT specialists.id_organization,
    self.id_specialization
   FROM personal_specializations self
     JOIN specialists ON specialists.id = self.id_specialist
     JOIN specializations ON specializations.id = self.id_specialization
     JOIN organizations ON organizations.id = specialists.id_organization
  WHERE specialists.expel_date IS NULL;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.real_organization_specializations IS 'Представление для вывода актуальных специализаций в организации'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_services AS 
 SELECT organizations.id AS id_organization,
    services.id AS id_service,
    service_types.id AS id_service_type,
    service_types.name AS service_type_name,
    service_types.sort_by AS service_type_sort,
    services.name AS service_name,
    services.sort_by AS service_sort,
    services.price AS service_price,
    services.duration AS service_duration,
    service_measures.id AS service_si,
    service_measures.name AS service_si_name,
    service_sign(services.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, organizations.id, services.id) AS sign,
    service_reason(services.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, cabinet_types.name::text, specializations.name::text, organizations.id, services.id) AS reason
   FROM gov_services services
     JOIN pricelists ON pricelists.id = services.id_pricelist
     JOIN organizations ON organizations.id = pricelists.id_organization
     JOIN specializations ON specializations.id = services.id_specialization
     JOIN service_types ON service_types.id = services.id_service_type
     LEFT JOIN service_measures ON service_measures.id = services.id_service_measure
     LEFT JOIN organization_cabinets ON organization_cabinets.id_organization = organizations.id AND organization_cabinets.id_cabinet_type = services.id_cabinet_type
     LEFT JOIN cabinet_types ON cabinet_types.id = services.id_cabinet_type
     LEFT JOIN real_organization_specializations ON real_organization_specializations.id_specialization = services.id_specialization AND real_organization_specializations.id_organization = organizations.id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_services IS 'Представление для вывода списка услуг организаций и их актуальности'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180726_121631_add_organization_services_functions cannot be reverted.\n";

        return false;
    }
}
