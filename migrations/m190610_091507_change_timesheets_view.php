<?php

use app\commands\migrate\Migration;

/**
 * Class m190610_091507_change_timesheets_view
 */
class m190610_091507_change_timesheets_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
  WHERE date_trunc('day'::text, COALESCE(specialists.expel_date::timestamp with time zone, now() + make_interval(days => 2))) >= date_trunc('day'::text, now()) AND upper(timesheets.date) >= now() AND shift_type.type::text = 'MOSRU_APPOINTMENT'::text AND date_trunc('day'::text, COALESCE(specialists.expel_date::timestamp with time zone, now() + make_interval(days => 31))) >= date_trunc('day'::text, lower(timesheets.date));

SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
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
  WHERE COALESCE(specialists.expel_date::timestamp with time zone, now() + make_interval(days => 2)) > now() AND upper(timesheets.date) >= now() AND shift_type.type::text = 'MOSRU_APPOINTMENT'::text AND COALESCE(specialists.expel_date::timestamp with time zone, now() + make_interval(days => 31)) > lower(timesheets.date);

SQL;
        $this->execute($sql);
    }
}
