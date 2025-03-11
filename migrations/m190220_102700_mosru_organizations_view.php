<?php

use app\commands\migrate\Migration;

/**
 * Class m190220_102700_mosru_organizations_view
 */
class m190220_102700_mosru_organizations_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.organizations AS 
 SELECT DISTINCT organizations.id,
    organizations.name,
    org_types.name AS type,
    organizations.id_address
   FROM organizations
     JOIN addresses ON organizations.id_address = addresses.id
     LEFT JOIN org_types ON org_types.id = organizations.id_org_type
     JOIN specialists ON specialists.id_organization = organizations.id
     JOIN services_specialists ON specialists.id = services_specialists.id_specialist AND organizations.id = services_specialists.id_organization
  WHERE specialists.expel_date > now() OR specialists.expel_date IS NULL
  ORDER BY organizations.name
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.organizations IS 'Организации оказывающие услуги для mos.ru'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW mosru.organizations");
    }

}
