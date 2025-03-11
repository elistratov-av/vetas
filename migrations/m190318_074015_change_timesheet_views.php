<?php

use app\commands\migrate\Migration;

/**
 * Class m190318_074015_change_timesheet_views
 */
class m190318_074015_change_timesheet_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW mosru.timesheets_slots");
        $this->execute("DROP VIEW mosru.timesheets");

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
        $this->execute("COMMENT ON VIEW mosru.timesheets
  IS 'Представление для вывода расписаний доступных для записи в mos.ru'");

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
          WHERE lower(timesheets.date) >= now()::date AND lower(timesheets.date) <= (now() + '30 days'::interval)::date
    ) t
  WHERE NOT (EXISTS ( SELECT 1
           FROM break_timesheets bt
          WHERE bt.id_specialist = t.id_specialist AND bt.date && tsrange(t.slot, t.slot + make_interval(mins => 10), '[)'::text)))
  AND NOT (EXISTS (SELECT 1 
            FROM organizations_emergency e
            WHERE t.id_organization = e.id_organization 
            AND t.slot BETWEEN e.date_from AND e.date_to))
  ORDER BY t.id_user, t.slot;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.timesheets_slots
  IS 'Представление для вывода слотов доступных для записи в mos.ru'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
