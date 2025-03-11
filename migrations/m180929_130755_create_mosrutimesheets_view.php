<?php

use app\commands\migrate\Migration;

/**
 * Class m180929_130755_create_mosrutimesheets_view
 */
class m180929_130755_create_mosrutimesheets_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_timesheets AS 
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
  WHERE shift_type.type::text = 'MOSRU_APPOINTMENT'::text;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru_timesheets IS 'Представление для вывода расписаний доступных для записи в mos.ru'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.mosru_timesheets");
    }

}
