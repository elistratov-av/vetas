<?php

use app\commands\migrate\Migration;

/**
 * Class m210617_140029_3386_new_timesheets_views
 */
class m210617_140029_3386_new_timesheets_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute(
            $this->getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS()
        );
        $this->execute("COMMENT ON VIEW mosru.timesheets_call_to_home IS 'Таймшиты с записью на дом mosru'");

        $this->execute(
            $this->getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS_SLOTS()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute(
            $this->getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS_SLOTS_OLD()
        );
        $this->execute('drop view if exists mosru.timesheets_call_to_home');
    }


    protected function getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS_SLOTS()
    {
        return <<<SQL
create or replace view mosru.timesheets_call_to_home_slots
            (date, slot_duration, slot, slot_range, id_organization, id_specialist, id_user) as
SELECT slots.date,
       slots.slot_duration,
       slots.slot,
       slots.slot_range,
       slots.id_organization,
       slots.id_specialist,
       slots.id_user
FROM (SELECT t.slot::date                                               AS date,
             t.slot_duration,
             t.slot,
             tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
             t.id_organization,
             t.id_specialist,
             t.id_user
      FROM (SELECT DISTINCT 10                                    AS slot_duration,
                            generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval,
                                            '00:10:00'::interval) AS slot,
                            timesheets.id_organization,
                            timesheets.id_specialist,
                            timesheets.id_user
            FROM mosru.timesheets_call_to_home timesheets
            WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
         LEFT JOIN public.specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND
                                              specialist_visits_range.status::text <> 'A'::text AND
                                              specialist_visits_range.status::text <> 'F'::text AND
                                              slots.slot_range && specialist_visits_range.visits_range
         LEFT JOIN public.break_timesheets_slots bts
                   ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND
                      bts.slot_range && slots.slot_range
WHERE specialist_visits_range.id IS NULL
  AND bts.slot_range IS NULL
  AND NOT (EXISTS(SELECT 1
                  FROM public.organizations_emergency e
                  WHERE slots.id_organization = e.id_organization
                    AND slots.slot >= e.date_from
                    AND slots.slot <= e.date_to));
SQL;

    }


    protected function getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS_SLOTS_OLD()
    {
        return <<<SQL
create or replace view mosru.timesheets_call_to_home_slots
            (date, slot_duration, slot, slot_range, id_organization, id_specialist, id_user) as
SELECT slots.date,
       slots.slot_duration,
       slots.slot,
       slots.slot_range,
       slots.id_organization,
       slots.id_specialist,
       slots.id_user
FROM (SELECT t.slot::date                                               AS date,
             t.slot_duration,
             t.slot,
             tsrange(t.slot, t.slot + '00:10:00'::interval, '[)'::text) AS slot_range,
             t.id_organization,
             t.id_specialist,
             t.id_user
      FROM (SELECT DISTINCT 10                                    AS slot_duration,
                            generate_series(lower(timesheets.date), upper(timesheets.date) - '00:10:00'::interval,
                                            '00:10:00'::interval) AS slot,
                            timesheets.id_organization,
                            timesheets.id_specialist,
                            timesheets.id_user
            FROM mosru.timesheets timesheets
            WHERE upper(timesheets.date) <= (now() + '30 days'::interval)::date) t) slots
         LEFT JOIN public.specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND
                                              specialist_visits_range.status::text <> 'A'::text AND
                                              specialist_visits_range.status::text <> 'F'::text AND
                                              slots.slot_range && specialist_visits_range.visits_range
         JOIN public.call_to_home_timesheets_slots cts
              ON cts.id_user = slots.id_user AND cts.id_organization = slots.id_organization AND
                 cts.slot_range && slots.slot_range
         LEFT JOIN public.break_timesheets_slots bts
                   ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND
                      bts.slot_range && slots.slot_range
WHERE specialist_visits_range.id IS NULL
  AND bts.slot_range IS NULL
  AND NOT (EXISTS(SELECT 1
                  FROM public.organizations_emergency e
                  WHERE slots.id_organization = e.id_organization
                    AND slots.slot >= e.date_from
                    AND slots.slot <= e.date_to));
SQL;

    }

    protected function getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS()
    {
        return <<<SQL
create view mosru.timesheets_call_to_home
            (id, id_organization, id_user, id_specialist, id_shift, created_by, updated_by, created_at, updated_at,
             date, parent_id)
as
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
FROM public.timesheets
         JOIN public.shifts ON shifts.id = timesheets.id_shift
         JOIN public.shift_type ON shift_type.id = shifts.id_type
         JOIN public.specialists ON specialists.id = timesheets.id_specialist
WHERE upper(timesheets.date) >= now()
  AND shift_type.type::text = 'MOSRU_CALL_TO_HOME'::text;

SQL;
    }



    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210617_140029_3386_new_timesheets_views cannot be reverted.\n";

        return false;
    }
    */
}
