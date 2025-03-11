<?php

use app\commands\migrate\Migration;

/**
 * Class m190404_141808_timeslotlist_optimization
 */
class m190404_141808_timeslotlist_optimization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP FUNCTION IF EXISTS mosru.get_slots(integer);");
        $this->execute("DROP TYPE IF EXISTS mosru.slot;");

        $sql = <<<SQL
CREATE TYPE mosru.slot AS
   (date date,
    work_time tsrange,
    slot_duration integer,
    slot timestamp without time zone,
    slot_range tsrange,
    id_organization integer,
    id_specialist integer,
    id_user integer,
    slots_number integer);
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION mosru.get_slots(id_user integer)
  RETURNS SETOF mosru.slot AS
\$BODY$
DECLARE
    query text;
    slots record;
    prev_slot tsrange;
    prev_organization integer;
    count integer;
BEGIN    
    query := '
select 
    *,
    1 AS slots_number
from mosru.timesheets_slots
where id_user = ' || id_user || '
order by id_organization, slot
';
    count := 1;
    FOR slots IN EXECUTE query LOOP
        IF (prev_slot IS NOT NULL AND NOT (prev_slot -|- slots.slot_range)) OR (prev_organization IS NOT NULL AND prev_organization != slots.id_organization) THEN
            count := count + 1;
        END IF;

        slots.slots_number := count;
        prev_slot := slots.slot_range;
        prev_organization := slots.id_organization;
        
        RETURN NEXT slots;
    END LOOP;
END;    
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 5000;
SQL;
        $this->execute($sql);

        $this->execute("DROP VIEW mosru.timesheets_slots;");
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT t.date,
    t.work_time,
    t.slot_duration,
    t.slot,
    tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
    t.id_organization,
    t.id_specialist,
    t.id_user
   FROM ( SELECT DISTINCT lower(timesheets.date)::date AS date,
            timesheets.date AS work_time,
            10 AS slot_duration,
            generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
            timesheets.id_organization,
            timesheets.id_specialist,
            timesheets.id_user
           FROM mosru.timesheets timesheets
          WHERE upper(timesheets.date) >= now()::date AND upper(timesheets.date) <= (now() + '30 days'::interval)::date) t
     LEFT JOIN specialist_visits_range ON t.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND specialist_visits_range.date = t.date AND (tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) = specialist_visits_range.visits_range OR tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) && specialist_visits_range.visits_range)
  WHERE NOT (EXISTS ( SELECT 1
           FROM break_timesheets bt
          WHERE bt.id_specialist = t.id_specialist AND bt.date && tsrange(t.slot, t.slot + make_interval(mins => 10), '[)'::text))) AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE t.id_organization = e.id_organization AND t.slot >= e.date_from AND t.slot <= e.date_to)) AND specialist_visits_range.id IS NULL
  ORDER BY t.id_user, t.slot;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.timesheets_slots
  IS 'Представление для вывода слотов доступных для записи в mos.ru';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP FUNCTION mosru.get_slots(integer);");
        $this->execute("DROP TYPE mosru.slot;");

        $this->execute("DROP VIEW mosru.timesheets_slots;");
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT t.date,
    t.work_time,
    t.slot,
    t.id_organization,
    t.id_specialist,
    t.id_user
   FROM ( SELECT DISTINCT lower(timesheets.date)::date AS date,
            timesheets.date AS work_time,
            generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
            timesheets.id_organization,
            timesheets.id_specialist,
            timesheets.id_user
           FROM mosru.timesheets timesheets
          WHERE lower(timesheets.date) >= now()::date AND lower(timesheets.date) <= (now() + '30 days'::interval)::date) t
  WHERE NOT (EXISTS ( SELECT 1
           FROM break_timesheets bt
          WHERE bt.id_specialist = t.id_specialist AND bt.date && tsrange(t.slot, t.slot + make_interval(mins => 10), '[)'::text))) AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE t.id_organization = e.id_organization AND t.slot >= e.date_from AND t.slot <= e.date_to))
  ORDER BY t.id_user, t.slot;
SQL;

        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.timesheets_slots
  IS 'Представление для вывода слотов доступных для записи в mos.ru';");
    }

}
