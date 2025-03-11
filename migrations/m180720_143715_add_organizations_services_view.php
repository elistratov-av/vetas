<?php

use yii\db\Migration;

/**
 * Class m180720_143715_add_organizations_services_view
 */
class m180720_143715_add_organizations_services_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
    service_sign(services.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, true) AS sign,
    service_reason(services.id_cabinet_type IS NOT NULL, real_organization_specializations.id_specialization IS NOT NULL, true, cabinet_types.name::text, specializations.name::text, ''::text) AS reason
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.real_organization_specializations");
        $this->execute("DROP VIEW public.organizations_services");
    }

}
