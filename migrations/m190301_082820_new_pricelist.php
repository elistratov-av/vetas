<?php

use app\commands\migrate\Migration;

/**
 * Class m190301_082820_new_pricelist
 */
class m190301_082820_new_pricelist extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW IF EXISTS public.organizations_active");
        $this->execute("DROP VIEW public.organizations_services");
        $this->execute("DROP VIEW public.organizations_price");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_price AS 
 SELECT organizations.id AS id_organization,
    services.id AS id_service,
    services.name AS service_name,
    services.sort_by AS service_sort,
    services.price AS service_price,
    services.duration AS service_duration,
    services.id_service_type,
    services.id_service_measure
   FROM gov_services services,
    organizations;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_price
  IS 'Вьюшка для представления услуг из одного прайслиста на  все организации';");

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
    service_measures.name AS service_si_name
   FROM organizations_price price
     JOIN service_types ON service_types.id = price.id_service_type
     LEFT JOIN service_measures ON service_measures.id = price.id_service_measure
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_services
  IS 'Представление для вывода списка услуг организаций и их актуальности';");


        $this->dropForeignKey('fk-gov_services-id_cabinet_type', 'gov_services');
        //$this->dropForeignKey('fk-gov_services-id_specialization', 'gov_services');

        $this->dropColumn('gov_services', 'id_cabinet_type');
        //$this->dropColumn('gov_services', 'id_specialization');

        $this->dropColumn('organizations', 'id_pricelist');

        $this->execute("DROP FUNCTION public.service_sign(boolean, boolean, integer, integer)");
        $this->execute("DROP FUNCTION public.service_reason(boolean, boolean, text, text, integer, integer);");
        $this->execute("DROP FUNCTION public.organization_balance_for_service(integer, integer)");
        $this->execute("DROP FUNCTION public.organization_on_balance(text, integer, integer)");
        $this->execute("DROP FUNCTION public.organization_on_balance(text, integer)");
        $this->execute("DROP FUNCTION public.organization_has_drugs(integer)");
        $this->execute("DROP FUNCTION public.organization_has_drugs(integer, integer)");
        $this->execute("DROP FUNCTION public.organization_has_equipments(integer, integer)");
        $this->execute("DROP FUNCTION public.organization_has_equipments(integer)");
        $this->execute("DROP FUNCTION public.organization_has_vaccine(integer, integer)");
        $this->execute("DROP FUNCTION public.organization_has_vaccine(integer)");

        $this->execute("DROP TABLE public.service_tmcs");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
