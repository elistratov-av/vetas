<?php

use yii\db\Migration;

/**
 * Class m180815_082503_refactor_visits_table
 */
class m180815_082503_refactor_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Создаем столбец формата tsrange
        $this->execute("ALTER TABLE public.visits ADD COLUMN time_range tsrange; ");

        // Заполняем данными
        $sql = '
UPDATE
	visits
SET
	time_range = subquery.datetime_range
FROM (
    SELECT 
        visits.id,
        tsrange(
          to_timestamp(visits.start_dttm::double precision)::timestamp without time zone,
          to_timestamp((visits.start_dttm + visits.duration * 60)::double precision)::timestamp without time zone,
          \'[)\'::text
        ) AS datetime_range
    FROM 
      visits
) AS subquery
WHERE
	subquery.id = visits.id;';

        $this->execute($sql);

        // Not null
        $this->execute('ALTER TABLE public.visits ALTER COLUMN time_range SET NOT NULL;');

        // Коммент
        $this->execute("COMMENT ON COLUMN public.visits.time_range IS 'Время визита';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE public.visits DROP COLUMN time_range; ");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_082503_refactor_visits_table cannot be reverted.\n";

        return false;
    }
    */
}
