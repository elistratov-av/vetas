<?php

use app\commands\migrate\Migration;

/**
 * Class m181009_072724_change_mosru_timesheets_slots_view
 */
class m181009_072724_change_mosru_timesheets_slots_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_timesheets_slots AS 
 SELECT DISTINCT lower(mosru_timesheets.date)::date AS date,
    mosru_timesheets.date AS work_time,
    generate_series(lower(mosru_timesheets.date), upper(mosru_timesheets.date) - '00:10:00'::interval, '00:10:00'::interval) AS slot,
    mosru_timesheets.id_specialist
   FROM mosru_timesheets
  WHERE lower(mosru_timesheets.date) >= now()::date AND lower(mosru_timesheets.date) <= (now() + '30 days'::interval)::date;
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
