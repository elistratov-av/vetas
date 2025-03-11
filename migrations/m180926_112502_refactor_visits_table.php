<?php

use app\commands\migrate\Migration;

/**
 * Class m180926_112502_refactor_visits_table
 */
class m180926_112502_refactor_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'number', $this->integer());
        $this->addColumn('visits', 'source', $this->integer());
        $this->addColumn('visits', 'duration', $this->integer());
        $this->addColumn('visits', 'start_specialization', $this->integer());
        $this->addColumn('visits', 'ticket_number', $this->string());
        $this->addColumn('visits', 'start_dttm', 'timestamp without time zone');

        $sql = <<<SQL
UPDATE
	visits
SET
	duration = subquery.minuts
FROM (
    select visits.id, extract(epoch from (select upper(vs.time_range) - lower(vs.time_range) from visits as vs where vs.id = visits.id))/60 as minuts from visits
    ) AS subquery
WHERE
	subquery.id = visits.id;
SQL;
        $this->execute($sql);

        $this->execute('UPDATE visits SET start_dttm = lower(time_range)');

        $this->execute('alter table visits alter column fact_start_dttm type timestamp without time zone using to_timestamp(fact_start_dttm)');
        $this->execute('alter table visits alter column fact_end_dttm type timestamp without time zone using to_timestamp(fact_end_dttm)');

        $sql = <<<SQL
update visits
set number = dc.rc
from (
  select id,
    row_number() over (partition by date_trunc('day',start_dttm)) as rc
  from visits
) as dc
where dc.id = visits.id
SQL;
        $this->execute($sql);
        $this->execute('alter table visits alter column number set not null;');


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180926_112502_refactor_visits_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_112502_refactor_visits_table cannot be reverted.\n";

        return false;
    }
    */
}
