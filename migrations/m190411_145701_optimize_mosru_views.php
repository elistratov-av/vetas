<?php

use app\commands\migrate\Migration;

/**
 * Class m190411_145701_optimize_mosru_views
 */
class m190411_145701_optimize_mosru_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW mosru.timesheets_slots");
        $this->execute("DROP VIEW mosru.timesheets");
        $this->execute("DROP VIEW public.break_timesheets");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.break_timesheets AS 
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
  WHERE upper(timesheets.date) >= now() AND shift_type.type::text = 'BREAK'::text;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.break_timesheets
  IS 'Представление для вывода перерывов на обед'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.break_timesheets_slots AS 
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
           FROM break_timesheets timesheets
          WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.break_timesheets_slots
  IS 'Представление для вывода слотов занятых под перерыв на обед'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets AS 
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
  WHERE upper(timesheets.date) >= now() AND shift_type.type::text = 'MOSRU_APPOINTMENT'::text;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.timesheets
  IS 'Представление для вывода расписаний доступных для записи в mos.ru';");

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
        $this->execute("COMMENT ON VIEW mosru.timesheets_slots
  IS 'Представление для вывода слотов доступных для записи в mos.ru';");

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
     JOIN visits_specialists ON visits_specialists.id_visit = visits.id
  WHERE visits.start_dttm >= now();
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.specialist_visits_range
  IS 'Временная view со списком занятых диапазонов у специалиста'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW mosru.timesheets_slots");
        $this->execute("DROP VIEW mosru.timesheets");
        $this->execute("DROP VIEW public.break_timesheets_slots;");
        $this->execute("DROP VIEW public.break_timesheets");

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.break_timesheets AS 
 SELECT timesheets.id,
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
  WHERE shift_type.type::text = 'BREAK'::text;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets AS 
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
  WHERE shift_type.type::text = 'MOSRU_APPOINTMENT'::text;
SQL;
        $this->execute($sql);

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
        $this->execute("COMMENT ON VIEW public.specialist_visits_range
  IS 'Временная view со списком занятых диапазонов у специалиста'");

    }

}
