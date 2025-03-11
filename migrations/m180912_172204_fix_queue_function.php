<?php

use app\commands\migrate\Migration;

/**
 * Class m180912_172204_fix_queue_function
 */
class m180912_172204_fix_queue_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.push_queue(
    visit_id integer,
    status character varying)
  RETURNS void AS
\$BODY$
DECLARE
    job text;
BEGIN
    job := '{"visit_id": ' || visit_id || ', "status": "' || status || '"}'::text;
    INSERT INTO public.queue(channel, job, pushed_at, delay, ttr, priority) 
    VALUES ('soap', job::bytea, extract(epoch from now())::integer, 0, 300, 1024);
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
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
