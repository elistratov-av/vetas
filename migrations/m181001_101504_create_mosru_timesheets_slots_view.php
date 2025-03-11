<?php

use app\commands\migrate\Migration;

/**
 * Class m181001_101504_change_mosru_timesheets_view
 */
class m181001_101504_create_mosru_timesheets_slots_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_timesheets_slots AS 
SELECT 
    lower(mosru_timesheets.date)::date as date,
    mosru_timesheets.date AS work_time, 
    generate_series(
        LOWER(mosru_timesheets.date), 
        UPPER(mosru_timesheets.date) - '10 minutes'::interval, 
        '10 minutes'::interval
    ) AS slot, 
    mosru_timesheets.id_specialist 
FROM mosru_timesheets
WHERE
  lower(mosru_timesheets.date) BETWEEN NOW()::date AND (NOW() + interval '30 days')::date
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru_timesheets_slots IS 'Представление для вывода слотов доступных для записи в mos.ru'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.mosru_timesheets_slots");
    }
}
