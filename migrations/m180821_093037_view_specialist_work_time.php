<?php

use yii\db\Migration;

/**
 * Class m180821_093037_view_specialist_work_time
 */
class m180821_093037_view_specialist_work_time extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = '
 CREATE OR REPLACE VIEW public.specialist_work_time AS 
 SELECT ts.id_specialist,
    ts.date,
    shifts.from_time,
    shifts.from_time + ((shifts.duration || \'minute\'::text)::interval) AS to_time,
    shifts.duration,
    ts.date + shifts.from_time AS ts_from,
    ts.date + shifts.from_time + ((shifts.duration || \'minute\'::text)::interval) AS ts_to,
    tsrange(ts.date + shifts.from_time, ts.date + shifts.from_time + ((shifts.duration || \'minute\'::text)::interval), \'[)\'::text) AS work_datetime_range
   FROM timesheets ts
     LEFT JOIN shifts ON shifts.id = ts.id_shift;
';
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.specialist_work_time 
        IS 'Временная view со диапазонами рабочего времени специалиста';");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW public.specialist_work_time;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180821_093037_view_specialist_work_time cannot be reverted.\n";

        return false;
    }
    */
}
