<?php

use app\commands\migrate\Migration;

/**
 * Class m180912_132413_add_change_visit_status_trigger
 */
class m180912_132413_add_change_visit_status_trigger extends Migration
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
    job bytea;
BEGIN
    job := '{"visit_id": ' || visit_id || '6, "status": "' || status || '"}'::bytea;
    INSERT INTO public.queue(channel, job, pushed_at, delay, ttr, priority) 
    VALUES ('soap', job, extract(epoch from now())::integer, 0, 300, 1024);
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.handle_visit_status()
  RETURNS trigger AS
\$BODY$
BEGIN
    IF OLD.status != NEW.status AND (EXISTS (SELECT 1 FROM etp.message WHERE visit_id = NEW.id)) THEN
        CASE
            WHEN NEW.status = 'C' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);        
            WHEN NEW.status = 'W' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
            WHEN NEW.status = 'A' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
            ELSE
                --nothing
        END CASE;
    END IF;

    RETURN NEW;
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER visits_change_status
  AFTER UPDATE
  ON public.visits
  FOR EACH ROW
  EXECUTE PROCEDURE public.handle_visit_status();
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER visits_change_status ON public.visits;");
        $this->execute("DROP FUNCTION public.handle_visit_status();");
        $this->execute("DROP FUNCTION public.push_queue(integer, character varying);");
    }

}
