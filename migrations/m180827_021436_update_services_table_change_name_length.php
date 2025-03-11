<?php

use app\commands\migrate\Migration;

/**
 * Class m180827_021436_update_services_table_change_name_length
 */
class m180827_021436_update_services_table_change_name_length extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW public.organizations_services");

        $this->alterColumn('services', 'name', $this->text());

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
        $this->execute("DROP VIEW public.organizations_services");

        $this->alterColumn('services', 'name', $this->string());

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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_021436_update_services_table_change_name_length cannot be reverted.\n";

        return false;
    }
    */
}
