<?php

use app\commands\migrate\Migration;

/**
 * Class m181025_131652_fix_specialist_visits_range_view
 */
class m181025_131652_fix_specialist_visits_range_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.specialist_visits_range AS 
 SELECT visits_specialists.id_specialist,
    visits.id,
    visits.status,
    visits.start_dttm::date AS date,
    visits.start_dttm AS start_time,
    visits.start_dttm + make_interval(mins => visits.duration + COALESCE(visits.cooldown, 0)) AS end_time,
    tsrange(visits.start_dttm, visits.start_dttm + make_interval(mins => visits.duration + COALESCE(visits.cooldown, 0)), '[)'::text) AS visits_range
   FROM visits
     JOIN visits_specialists ON visits_specialists.id_visit = visits.id;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.specialist_visits_range AS 
 SELECT visits_specialists.id_specialist,
    visits.id,
    visits.status,
    visits.start_dttm::date AS date,
    visits.start_dttm AS start_time,
    visits.start_dttm + make_interval(mins => visits.duration) AS end_time,
    tsrange(visits.start_dttm, visits.start_dttm + make_interval(mins => visits.duration), '[)'::text) AS visits_range
   FROM visits
     JOIN visits_specialists ON visits_specialists.id_visit = visits.id;
SQL;
        $this->execute($sql);
    }

}
