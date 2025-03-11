<?php

use app\commands\migrate\Migration;

/**
 * Class m190222_070137_change_mosru_services_view
 */
class m190222_070137_change_mosru_services_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW etp.visits_mosru_services");
        $this->execute("DROP VIEW public.mosru_services_view");
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.services AS
 SELECT 
    *
  FROM gov_services
  WHERE type = 'mosru'
  ORDER BY id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.services IS 'Услуги mos.ru'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW etp.visits_mosru_services AS 
 SELECT visits_gov_services.id_visit,
    mosru.services.id,
    mosru.services.name
   FROM visits_gov_services
     LEFT JOIN mosru.services ON mosru.services.id = visits_gov_services.id_service;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW etp.visits_mosru_services");
        $this->execute("DROP VIEW mosru.services");
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_services_view AS
 SELECT 
    id, id_service_goal, id_service_type, name, at_home, true AS at_clinic, sort_by
  FROM gov_services
  WHERE type = 'mosru'
  ORDER BY id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.mosru_services IS 'Услуги mos.ru'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW etp.visits_mosru_services AS 
 SELECT visits_gov_services.id_visit,
    mosru_services.id,
    mosru_services.name
   FROM visits_gov_services
     LEFT JOIN mosru_services ON mosru_services.id = visits_gov_services.id_service;
SQL;
        $this->execute($sql);
    }

}
