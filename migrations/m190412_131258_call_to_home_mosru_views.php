<?php

use app\commands\migrate\Migration;

/**
 * Class m190412_131258_call_to_home_mosru_views
 */
class m190412_131258_call_to_home_mosru_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.call_to_home_timesheets AS 
 SELECT timesheets.id,
    specialists.id_organization,
    specialists.id_user,
    timesheets.id_specialist,
    timesheets.id_shift,
    timesheets.created_by,
    timesheets.updated_by,
    timesheets.created_at,
    timesheets.updated_at,
    timesheets.date,
    timesheets.parent_id
   FROM timesheets
     JOIN shifts ON shifts.id = timesheets.id_shift
     JOIN shift_type ON shift_type.id = shifts.id_type
     JOIN specialists ON specialists.id = timesheets.id_specialist
  WHERE upper(timesheets.date) >= now() AND shift_type.type::text = 'CALL_TO_HOME'::text;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.call_to_home_timesheets_slots AS 
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
           FROM call_to_home_timesheets timesheets
          WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t;
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

        $sql = <<<SQL
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
        $this->execute("COMMENT ON FUNCTION mosru.get_slots(integer, boolean) IS 'Функция для получения доступных слотов записи mos.ru по id_user'");

        $sql = <<<SQL
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
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION mosru.get_slots(integer, integer) IS 'Функция для получения доступных слотов записи mos.ru по id_user и id_organization'");

        $this->execute("DROP FUNCTION mosru.get_slots(integer);");
        $this->execute("DROP FUNCTION mosru.get_slots(integer, integer);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
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
    slots.*,
    1 AS slots_number
from mosru.timesheets_slots as slots
where 
    slots.id_user = ' || id_user || ' 
order by slots.id_organization, slots.slot
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

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION mosru.get_slots(
    id_user integer,
    id_organization integer)
  RETURNS SETOF mosru.slot AS
\$BODY$
DECLARE
    query text;
    slots record;
    prev_slot tsrange;
    count integer;
BEGIN    
    query := '
select 
    slots.*,
    1 AS slots_number
from mosru.timesheets_slots as slots
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
        $this->execute($sql);

        $this->execute("DROP VIEW mosru.timesheets_call_to_home_slots;");
        $this->execute("DROP VIEW public.call_to_home_timesheets_slots;");
        $this->execute("DROP VIEW public.call_to_home_timesheets;");

        $this->execute("DROP FUNCTION mosru.get_slots(integer, boolean);");
        $this->execute("DROP FUNCTION mosru.get_slots(integer, integer, boolean);");
    }

}
