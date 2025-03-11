<?php

use app\commands\migrate\Migration;

/**
 * Class m230707_053705_add_mos_ru_booking
 */
class m230707_053705_add_mos_ru_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('mosru.booking', [
            'id' => $this->primaryKey(),
            'id_specialist' => $this->integer(),
            'id_owner' => $this->integer(),
            'created_at' => $this->dateTime(),
        ]);

        $this->addForeignKey(
            'fk-booking-id_specialist',
            'mosru.booking',
            'id_specialist',
            'public.specialists',
            'id'
        );

        $this->addForeignKey(
            'fk-booking-id_owner',
            'mosru.booking',
            'id_owner',
            'public.pet_owners',
            'id'
        );

        $this->execute("ALTER TABLE mosru.booking ADD COLUMN time_range tsrange; ");

        $this->execute(
            $this->getSQL_CALL_TO_HOME_MOSRU_TIMESHEETS_SLOTS()
        );
        $this->execute("COMMENT ON VIEW mosru.timesheets_call_to_home IS 'Таймшиты с записью на дом mosru'");

        $this->execute(
            $this->getSQL_MOSRU_TIMESHEETS_SLOTS()
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

        $this->execute(
            $this->getSQL_MOSRU_TIMESHEETS_SLOTS_OLD()
        );

        $this->dropTable('mosru.booking');
    }

    protected function getSQL_MOSRU_TIMESHEETS_SLOTS()
    {
        return <<<SQL
create or replace view mosru.timesheets_slots AS
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
         LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND
                                              specialist_visits_range.status::text <> 'A'::text AND
                                              specialist_visits_range.status::text <> 'F'::text AND
                                              slots.slot_range && specialist_visits_range.visits_range                  
         LEFT JOIN mosru.booking ON slots.id_specialist = booking.id_specialist AND 
                                    slots.slot_range && booking.time_range AND
                                    booking.created_at > (now() - '00:15:00'::interval)
         LEFT JOIN break_timesheets_slots bts
                   ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND
                      bts.slot_range && slots.slot_range
WHERE specialist_visits_range.id IS NULL
  AND booking.id IS NULL
  AND bts.slot_range IS NULL
  AND NOT (EXISTS(SELECT 1
                  FROM organizations_emergency e
                  WHERE slots.id_organization = e.id_organization
                    AND slots.slot >= e.date_from
                    AND slots.slot <= e.date_to))
SQL;
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
         LEFT JOIN mosru.booking ON slots.id_specialist = booking.id_specialist AND 
                                    slots.slot_range && booking.time_range AND
                                    booking.created_at > (now() - '00:15:00'::interval)
         LEFT JOIN public.break_timesheets_slots bts
                   ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND
                      bts.slot_range && slots.slot_range
WHERE specialist_visits_range.id IS NULL
  AND booking.id IS NULL
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

    protected function getSQL_MOSRU_TIMESHEETS_SLOTS_OLD()
    {
        return <<<SQL
CREATE OR REPLACE VIEW mosru.timesheets_slots AS 
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
         LEFT JOIN specialist_visits_range ON slots.id_specialist = specialist_visits_range.id_specialist AND
                                              specialist_visits_range.status::text <> 'A'::text AND
                                              specialist_visits_range.status::text <> 'F'::text AND
                                              slots.slot_range && specialist_visits_range.visits_range
         LEFT JOIN break_timesheets_slots bts
                   ON bts.id_user = slots.id_user AND bts.id_organization = slots.id_organization AND
                      bts.slot_range && slots.slot_range
WHERE specialist_visits_range.id IS NULL
  AND bts.slot_range IS NULL
  AND NOT (EXISTS(SELECT 1
                  FROM organizations_emergency e
                  WHERE slots.id_organization = e.id_organization
                    AND slots.slot >= e.date_from
                    AND slots.slot <= e.date_to))
SQL;
    }
}
