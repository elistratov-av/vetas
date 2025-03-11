<?php

use yii\db\Migration;

/**
 * Class m180814_072616_refactor_timesheet_table
 */
class m180814_072616_refactor_timesheet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Создаем столбец формата tsrange
        $this->execute("ALTER TABLE public.timesheets ADD COLUMN timing tsrange; ");

        // Заполняем данными
        $sql = '
UPDATE
	timesheets
SET
	timing = subquery.work_datetime_range
FROM (
	SELECT 
		ts.id,
		tsrange(
		  ts.date + shifts.from_time,
          ts.date + shifts.from_time + ((shifts.duration || \'minute\'::text)::interval),
          \'[)\'::text
       ) AS work_datetime_range
	FROM 
		timesheets ts
	LEFT JOIN 
		shifts ON shifts.id = ts.id_shift
) AS subquery
WHERE
	subquery.id = timesheets.id;';

        $this->execute($sql);

        // Not null
        $this->execute('ALTER TABLE public.timesheets ALTER COLUMN timing SET NOT NULL;');

        // Коммент
        $this->execute("COMMENT ON COLUMN public.timesheets.timing IS 'Рабочий день специалиста';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE public.timesheets DROP COLUMN timing; ");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180814_072616_refactor_timesheet_table cannot be reverted.\n";

        return false;
    }
    */
}
