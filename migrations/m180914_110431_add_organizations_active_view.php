<?php

use app\commands\migrate\Migration;

/**
 * Class m180914_110431_add_organizations_active_view
 */
class m180914_110431_add_organizations_active_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.service_sign(
    cabinet_exists boolean,
    specialist_exists boolean,
    organization_id integer,
    service_id integer)
  RETURNS boolean AS
\$BODY$
begin
    -- временно убираем проверку баланса, т.к. он не заполнен у организаций
    --return cabinet_exists AND specialist_exists AND (EXISTS (SELECT 1 from public.organization_balance_for_service(organization_id, service_id) where available));
    return cabinet_exists AND specialist_exists;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

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
        -- временно убираем проверку баланса, т.к. он не заполнен у организаций
        /*
        WHEN NOT EXISTS (SELECT 1 from public.organization_balance_for_service(organization_id, service_id) where available) THEN            
            FOR rows IN SELECT * FROM public.organization_balance_for_service(organization_id, service_id) where NOT available LOOP
                CASE 
                    WHEN rows.service_tmc_class='equipment' THEN name := 'оборудования';
                    WHEN rows.service_tmc_class='drug' THEN name := 'препаратов';
                    WHEN rows.service_tmc_class='vaccine' THEN name := 'вакцин';
                    ELSE name := rows.service_tmc_class;
                END CASE;

                names := array_append(names, COALESCE(rows.name, name, '')::text);
            END LOOP;

            IF COALESCE(result, '') = '' THEN
                result := 'Отсутствуют необходимые ТМЦ на балансе';
            ELSE
                result := 'Нет на балансе: ' || array_to_string(names, ', ');
            END IF;
        */
        ELSE result := '';
    END CASE;

    RETURN result;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_price AS 
 SELECT organizations.id AS id_organization,
    services.id AS id_service,
    services.name AS service_name,
    services.sort_by AS service_sort,
    services.price AS service_price,
    services.duration AS service_duration,
    services.id_cabinet_type,
    services.id_specialization,
    services.id_service_type,
    services.id_service_measure
   FROM gov_services services,
    organizations;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_price 
            IS 'Вьюшка для представления услуг из одного прайслиста на  все организации'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_services AS 
 SELECT price.id_organization,
    price.id_service,
    service_types.id AS id_service_type,
    service_types.name AS service_type_name,
    service_types.sort_by AS service_type_sort,
    price.service_name,
    price.service_sort,
    price.service_price,
    price.service_duration,
    service_measures.id AS service_si,
    service_measures.name AS service_si_name,
    service_sign(price.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, price.id_organization, price.id_service) AS sign,
    service_reason(price.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, cabinet_types.name::text, specializations.name::text, price.id_organization, price.id_service) AS reason
   FROM organizations_price price
     JOIN specializations ON specializations.id = price.id_specialization
     JOIN service_types ON service_types.id = price.id_service_type
     LEFT JOIN service_measures ON service_measures.id = price.id_service_measure
     LEFT JOIN organization_cabinets ON organization_cabinets.id_organization = price.id_organization AND organization_cabinets.id_cabinet_type = price.id_cabinet_type
     LEFT JOIN cabinet_types ON cabinet_types.id = price.id_cabinet_type
     LEFT JOIN real_organization_specializations ON real_organization_specializations.id_specialization = price.id_specialization AND real_organization_specializations.id_organization = price.id_organization;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_services
            IS 'Представление для вывода списка услуг организаций и их актуальности';");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_active AS 
 SELECT DISTINCT (org.id),
org.parent_id,
org.id_org_type,
org.name,
org.short_name,
org.inn,
org.kpp,
org.ogrn,
org.id_address,
org.schedule,
org.id_area,
org.id_district,
org.created_by,
org.updated_by,
org.created_at,
org.updated_at,
org.id_pricelist
FROM organizations as org, organizations_services as srv
WHERE org.id = srv.id_organization AND srv.sign IS TRUE;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_active
            IS 'Представление для вывода списка организаций, которые оказывают услуги';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180914_110431_add_organizations_active_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180914_110431_add_organizations_active_view cannot be reverted.\n";

        return false;
    }
    */
}
