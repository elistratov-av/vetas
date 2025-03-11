<?php

use app\commands\migrate\Migration;

/**
 * Class m190423_083638_fix_mosru_timesheets_task_1806
 */
class m190423_083638_fix_mosru_timesheets_task_1806 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT slots.date,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.slot::date AS date,
            t.slot_duration,
            t.slot,
            tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
            t.id_organization,
            t.id_specialist,
            t.id_user
           FROM ( SELECT DISTINCT 
                    10 AS slot_duration,
                    generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
                    timesheets.id_organization,
                    timesheets.id_specialist,
                    timesheets.id_user
                   FROM mosru.timesheets timesheets
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND slots.slot_range && specialist_visits_range.visits_range
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
   FROM ( SELECT t.slot::date AS date,
            t.slot_duration,
            t.slot,
            tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
            t.id_organization,
            t.id_specialist,
            t.id_user
           FROM ( SELECT DISTINCT
                    10 AS slot_duration,
                    generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
                    timesheets.id_organization,
                    timesheets.id_specialist,
                    timesheets.id_user
                   FROM mosru.timesheets timesheets
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND specialist_visits_range.status::text <> 'A'::text AND specialist_visits_range.status::text <> 'F'::text AND slots.slot_range && specialist_visits_range.visits_range
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
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
 SELECT slots.date,
    slots.slot_duration,
    slots.slot,
    slots.slot_range,
    slots.id_organization,
    slots.id_specialist,
    slots.id_user
   FROM ( SELECT t.slot::date AS date,
            t.slot_duration,
            t.slot,
            tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
            t.id_organization,
            t.id_specialist,
            t.id_user
           FROM ( SELECT 
                    10 AS slot_duration,
                    generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
                    timesheets.id_organization,
                    timesheets.id_specialist,
                    timesheets.id_user
                   FROM mosru.timesheets timesheets
                  WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
     LEFT JOIN specialist_visits_range ON 
            slots.id_specialist = specialist_visits_range.id_specialist 
            AND specialist_visits_range.status::text <> 'A'::text 
            AND specialist_visits_range.status::text <> 'F'::text 
            AND specialist_visits_range.date = slots.slot::date 
            AND (slots.slot_range = specialist_visits_range.visits_range OR slots.slot_range && specialist_visits_range.visits_range)
     LEFT JOIN break_timesheets_slots bts ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND bts.slot_range && slots.slot_range
  WHERE specialist_visits_range.id IS NULL AND bts.slot_range IS NULL AND NOT (EXISTS ( SELECT 1
           FROM organizations_emergency e
          WHERE slots.id_organization = e.id_organization AND slots.slot >= e.date_from AND slots.slot <= e.date_to))
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

}
