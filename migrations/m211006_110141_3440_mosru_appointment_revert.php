<?php

use app\commands\migrate\Migration;

/**
 * Class m211006_110141_3440_mosru_appointment_revert
 */
class m211006_110141_3440_mosru_appointment_revert extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP FUNCTION mosru.get_slots(integer,boolean)');
        $this->execute('DROP FUNCTION mosru.get_slots(integer,integer, boolean) ');
        /*
         * get_slots(id_user integer, call_to_home boolean)
         */
        $sql = <<<SQL
CREATE FUNCTION mosru.get_slots(id_user integer, call_to_home boolean) returns SETOF mosru.slot
    rows 5000
    language plpgsql
as
$$
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
$$;
SQL;
        $this->execute($sql);

        /*
         * get_slots(id_user integer, id_organization integer, call_to_home boolean)
         */
        $sql = <<<SQL
CREATE FUNCTION mosru.get_slots(id_user integer, id_organization integer, call_to_home boolean) returns SETOF mosru.slot
    rows 5000
    language plpgsql
as
$$
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
$$;
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211006_110141_3440_mosru_appointment_revert cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211006_110141_3440_mosru_appointment_revert cannot be reverted.\n";

        return false;
    }
    */
}
