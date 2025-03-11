<?php

use app\commands\migrate\Migration;

/**
 * Class m181011_135529_fix_exists_range_function
 */
class m181011_135529_fix_exists_range_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.exists_range(
    from_date timestamp without time zone,
    to_date timestamp without time zone,
    duration interval)
  RETURNS boolean AS
\$BODY$
DECLARE
    query text;
    range_records record;
    prev_work_time tsrange;
    prev_slot tsrange;
    prev_specialist integer;
    tmp_duration interval;
    new_range boolean;
BEGIN
    query := '
SELECT
    DISTINCT working_time_slots_range.* 
FROM (
    SELECT
        date,
        work_time,
        slot::time,
        tsrange(slot, slot + ''10 minutes''::interval, ''[)''::text) AS slot_range,
        ''10 minutes''::interval as slot_duration,
        id_specialist
    FROM mosru_timesheets_slots
    WHERE        
        (mosru_timesheets_slots.work_time * tsrange(''' || from_date || ''', ''' || to_date || ''', ''[]''::text)) != ''empty'' 
        AND (upper(work_time) - lower(work_time))::interval >= ''' || duration || '''::interval
) AS working_time_slots_range 
LEFT JOIN specialist_visits_range  ON specialist_visits_range.status <> ''A'' AND working_time_slots_range.id_specialist = specialist_visits_range.id_specialist
    AND specialist_visits_range.date = working_time_slots_range.date 
    AND (
        working_time_slots_range.slot_range = specialist_visits_range.visits_range 
        OR working_time_slots_range.slot_range && specialist_visits_range.visits_range 
    )
WHERE
    specialist_visits_range.id IS NULL 
    AND slot_range && tsrange(''' || from_date || ''', ''' || to_date || ''') 
ORDER BY
    id_specialist, slot_range    
    ';

    --raise notice '%', query;

    new_range := true;
    FOR range_records IN EXECUTE query LOOP
        IF prev_work_time != range_records.work_time OR prev_specialist != range_records.id_specialist THEN
            new_range := true;
        ELSIF prev_slot IS NOT NULL AND NOT (prev_slot -|- range_records.slot_range) THEN
            new_range := true;
        END IF;
        
        IF new_range THEN
            new_range := false;

            tmp_duration := range_records.slot_duration;
        ELSE
            tmp_duration := tmp_duration + range_records.slot_duration;
        END IF;

        -- если времени хватает для према
        IF tmp_duration >= duration THEN
            return true;            
        END IF;

        prev_slot := range_records.slot_range;
        prev_work_time := range_records.work_time;
        prev_specialist := range_records.id_specialist;
    END LOOP;

    RETURN false;
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.exists_range(
    from_date timestamp without time zone,
    to_date timestamp without time zone,
    duration interval,
    id_specialist integer)
  RETURNS boolean AS
\$BODY$
DECLARE
    query text;
    range_records record;
    prev_work_time tsrange;
    prev_slot tsrange;
    prev_specialist integer;
    tmp_duration interval;
    new_range boolean;
BEGIN
    query := '
SELECT
    DISTINCT working_time_slots_range.* 
FROM (
    SELECT
        date,
        work_time,
        slot::time,
        tsrange(slot, slot + ''10 minutes''::interval, ''[)''::text) AS slot_range,
        ''10 minutes''::interval as slot_duration,
        id_specialist
    FROM mosru_timesheets_slots
    WHERE
        mosru_timesheets_slots.id_specialist = ' || id_specialist || '
        AND (mosru_timesheets_slots.work_time * tsrange(''' || from_date || ''', ''' || to_date || ''', ''[]''::text)) != ''empty'' 
        AND (upper(work_time) - lower(work_time))::interval >= ''' || duration || '''::interval
) AS working_time_slots_range 
LEFT JOIN specialist_visits_range  ON specialist_visits_range.status <> ''A'' AND working_time_slots_range.id_specialist = specialist_visits_range.id_specialist
    AND specialist_visits_range.date = working_time_slots_range.date 
    AND (
        working_time_slots_range.slot_range = specialist_visits_range.visits_range 
        OR working_time_slots_range.slot_range && specialist_visits_range.visits_range 
    )
WHERE
    specialist_visits_range.id IS NULL 
    AND slot_range && tsrange(''' || from_date || ''', ''' || to_date || ''') 
ORDER BY
    id_specialist, slot_range    
    ';

    --raise notice '%', query;

    new_range := true;
    FOR range_records IN EXECUTE query LOOP
        IF prev_work_time != range_records.work_time OR prev_specialist != range_records.id_specialist THEN
            new_range := true;
        ELSIF prev_slot IS NOT NULL AND NOT (prev_slot -|- range_records.slot_range) THEN
            --raise notice '% | %', prev_slot, range_records.slot_range;
            new_range := true;
        END IF;
        
        IF new_range THEN
            new_range := false;

            tmp_duration := range_records.slot_duration;
        ELSE
            tmp_duration := tmp_duration + range_records.slot_duration;
        END IF;

        --raise notice '%', tmp_duration;
        -- если времени хватает для према
        IF tmp_duration >= duration THEN
            return true;            
        END IF;

        prev_slot := range_records.slot_range;
        prev_work_time := range_records.work_time;
        prev_specialist := range_records.id_specialist;
    END LOOP;

    RETURN false;
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
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
