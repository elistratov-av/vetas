<?php

use app\commands\migrate\Migration;

/**
 * Class m190411_144624_optimize_mosru_queries
 */
class m190411_144624_optimize_mosru_queries extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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
        $this->execute("COMMENT ON FUNCTION mosru.get_slots(integer) IS 'Функция для получения доступных слотов записи mos.ru по id_user'");


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
        $this->execute("COMMENT ON FUNCTION mosru.get_slots(integer, integer) IS 'Функция для получения доступных слотов записи mos.ru по id_user и id_organization'");
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

        $this->execute("DROP FUNCTION mosru.get_slots(integer, integer);");
    }
}
