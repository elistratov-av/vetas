<?php

use app\commands\migrate\Migration;

/**
 * Class m181123_120319_change_species_mosru_service_view
 */
class m181123_120319_change_species_mosru_service_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.species_mosru_service AS 
 SELECT DISTINCT msgs.id_mosru_service,
    species_services.id_species
   FROM species_services
     JOIN mosru_services_gov_services msgs ON species_services.id_service = msgs.id_gov_services
  ORDER BY msgs.id_mosru_service;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.species_mosru_service AS 
 SELECT msgs.id_mosru_service,
    species_services.id_species
   FROM species_services
     JOIN mosru_services_gov_services msgs ON species_services.id_service = msgs.id_gov_services
  ORDER BY msgs.id_mosru_service;
SQL;
        $this->execute($sql);
    }
}
