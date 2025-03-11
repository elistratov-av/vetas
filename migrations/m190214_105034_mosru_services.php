<?php

use app\commands\migrate\Migration;

/**
 * Class m190313_072345_mosru_services
 */
class m190214_105034_mosru_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('gov_services_rating_mosru_services_id_fkey', 'statistic.mosru_services_rating');

        $mosruServices = $this->db->createCommand("
select
    gov_services.id as new_id,
    mosru_services.id as old_id
from gov_services
join mosru_services on mosru_services.name = gov_services.name AND gov_services.type = 'mosru'
          ")
            ->queryAll();

        foreach ($mosruServices as $item) {
            $this->update(
                'statistic.mosru_services_rating',
                ['mosru_services_id' => $item['new_id']],
                ['mosru_services_id' => $item['old_id']]
            );
        }

        $this->addForeignKey(
            'fk-mosru_services_rating-mosru_services_id',
            'statistic.mosru_services_rating',
            'mosru_services_id',
            'gov_services',
            'id'
        );
        $this->execute("DROP VIEW public.species_mosru_service");
        $this->execute("DROP VIEW admin.mosru_services_admin");
        $this->execute("DROP VIEW etp.visits_mosru_services");
        $this->execute("DROP VIEW statistic.statistic_mos_ru");

        $this->execute("DROP VIEW mosru_services_view");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_services_view AS
 SELECT
    id, id_service_goal, id_service_type, name, at_home, true AS at_clinic, sort_by
  FROM gov_services
  WHERE type = 'mosru'
  ORDER BY id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.mosru_services_view IS 'Услуги mos.ru'");

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
