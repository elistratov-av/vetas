<?php

use app\commands\migrate\Migration;

/**
 * Class m190422_084432_fix_timesheets_views_and_functions
 */
class m190422_084432_fix_timesheets_views_and_functions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP FUNCTION mosru.get_slots(integer, boolean)');
        $this->execute('DROP FUNCTION mosru.get_slots(integer, integer, boolean)');
        $this->execute('DROP TYPE mosru.slot');
        $this->execute('DROP VIEW mosru.timesheets_slots');
        $this->execute('DROP VIEW mosru.timesheets_call_to_home_slots');

        $sql = <<<SQL
CREATE TYPE mosru.slot AS
   (date date,
    slot_duration integer,
    slot timestamp without time zone,
    slot_range tsrange,
    id_organization integer,
    id_specialist integer,
    id_user integer,
    slots_number integer);
SQL;
        $this->execute($sql);
        $this->execute($this->getMosruSlotsSQL());
        $this->execute($this->getMosruSlotsByOrgSQL());

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT slots.date,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.date,
            t.slot_duration,
            t.slot,
            tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
            t.id_organization,
            t.id_specialist,
            t.id_user
           FROM ( SELECT DISTINCT lower(timesheets.date)::date AS date,
                    10 AS slot_duration,
                    generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
                    timesheets.id_organization,
                    timesheets.id_specialist,
                    timesheets.id_user
                   FROM mosru.timesheets timesheets
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND specialist_visits_range.date = slots.date AND (slots.slot_range = specialist_visits_range.visits_range OR slots.slot_range && specialist_visits_range.visits_range)
     LEFT JOIN break_timesheets_slots bts ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND bts.slot_range && slots.slot_range
  WHERE specialist_visits_range.id IS NULL AND bts.slot_range IS NULL AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE slots.id_organization = e.id_organization AND slots.slot >= e.date_from AND slots.slot <= e.date_to));
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_call_to_home_slots AS 
 SELECT slots.date,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.date,
            t.slot_duration,
            t.slot,
            tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
            t.id_organization,
            t.id_specialist,
            t.id_user
           FROM ( SELECT DISTINCT lower(timesheets.date)::date AS date,
                    10 AS slot_duration,
                    generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
                    timesheets.id_organization,
                    timesheets.id_specialist,
                    timesheets.id_user
                   FROM mosru.timesheets timesheets
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND specialist_visits_range.date = slots.date AND (slots.slot_range = specialist_visits_range.visits_range OR slots.slot_range && specialist_visits_range.visits_range)
     JOIN call_to_home_timesheets_slots cts ON cts.id_user = slots.id_user AND cts.id_organization = slots.id_organization AND cts.slot_range && slots.slot_range
     LEFT JOIN break_timesheets_slots bts ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND bts.slot_range && slots.slot_range
  WHERE specialist_visits_range.id IS NULL AND bts.slot_range IS NULL AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE slots.id_organization = e.id_organization AND slots.slot >= e.date_from AND slots.slot <= e.date_to));
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP FUNCTION mosru.get_slots(integer, boolean)');
        $this->execute('DROP FUNCTION mosru.get_slots(integer, integer, boolean)');
        $this->execute('DROP TYPE mosru.slot');
        $this->execute('DROP VIEW mosru.timesheets_slots');
        $this->execute('DROP VIEW mosru.timesheets_call_to_home_slots');

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
        $this->execute($this->getMosruSlotsSQL());
        $this->execute($this->getMosruSlotsByOrgSQL());

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT slots.date,
    slots.work_time,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.date,
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
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND specialist_visits_range.date = slots.date AND (slots.slot_range = specialist_visits_range.visits_range OR slots.slot_range && specialist_visits_range.visits_range)
     LEFT JOIN break_timesheets_slots bts ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND bts.slot_range && slots.slot_range
  WHERE specialist_visits_range.id IS NULL AND bts.slot_range IS NULL AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE slots.id_organization = e.id_organization AND slots.slot >= e.date_from AND slots.slot <= e.date_to));
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_call_to_home_slots AS 
 SELECT slots.date,
    slots.work_time,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.date,
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
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND specialist_visits_range.date = slots.date AND (slots.slot_range = specialist_visits_range.visits_range OR slots.slot_range && specialist_visits_range.visits_range)
     JOIN call_to_home_timesheets_slots cts ON cts.id_user = slots.id_user AND cts.id_organization = slots.id_organization AND cts.slot_range && slots.slot_range
     LEFT JOIN break_timesheets_slots bts ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND bts.slot_range && slots.slot_range
  WHERE specialist_visits_range.id IS NULL AND bts.slot_range IS NULL AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE slots.id_organization = e.id_organization AND slots.slot >= e.date_from AND slots.slot <= e.date_to));
SQL;
        $this->execute($sql);
    }

    /**
     * @return string
     */
    protected function getMosruSlotsSQL()
    {
        return <<<SQL
CREATE OR REPLACE FUNCTION mosru.get_slots(
    id_user integer,
    call_to_home boolean)
  RETURNS SETOF mosru.slot AS
\$BODY$
DECLARE
    query text;
    from_view text;
    slots record;
    prev_slot tsrange;
    prev_organization integer;
    count integer;
BEGIN    
    IF call_to_home THEN
        from_view := 'mosru.timesheets_call_to_home_slots';
    ELSE
        from_view := 'mosru.timesheets_slots';
    END IF;
    
    query := '
select 
    slots.*,
    1 AS slots_number
from ' || from_view|| ' as slots
where 
    slots.id_user = ' || id_user || ' 
order by slots.id_organization, slots.slot
';
    raise notice '%', query;
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
    }

    protected function getMosruSlotsByOrgSQL()
    {
        return <<<SQL
CREATE OR REPLACE FUNCTION mosru.get_slots(
    id_user integer,
    id_organization integer,
    call_to_home boolean)
  RETURNS SETOF mosru.slot AS
\$BODY$
DECLARE
    query text;
    from_view text;
    slots record;
    prev_slot tsrange;
    count integer;
BEGIN
    IF call_to_home THEN
        from_view := 'mosru.timesheets_call_to_home_slots';
    ELSE
        from_view := 'mosru.timesheets_slots';
    END IF;
    
    query := '
select 
    slots.*,
    1 AS slots_number
from ' || from_view || ' as slots
where 
    slots.id_user = ' || id_user || ' 
    and slots.id_organization = ' || id_organization || '
order by slots.id_organization, slots.slot
';
    count := 1;
    FOR slots IN EXECUTE query LOOP
        IF (prev_slot IS NOT NULL AND NOT (prev_slot -|- slots.slot_range)) THEN
            count := count + 1;
        END IF;

        slots.slots_number := count;
        prev_slot := slots.slot_range;
        
        RETURN NEXT slots;
    END LOOP;
END;    
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 5000;
SQL;

    }
}
