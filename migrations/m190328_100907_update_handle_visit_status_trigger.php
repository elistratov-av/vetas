<?php

use app\commands\migrate\Migration;

/**
 * Class m190328_100907_update_handle_visit_status_trigger
 */
class m190328_100907_update_handle_visit_status_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.handle_visit_status()
  RETURNS trigger AS
\$BODY$
BEGIN
    /*
        Статус "C1" - изменение со стороны ЕТП. В этом случае генерим событие в очередь, для отправки ответа В ЕТП
        при этом сам статус меняем на "C", т.к. у нас в системе статус "C1" не предусмотрен.
            
        Статус "A1" - отмена со стороны ЕТП. В этом случае генерим событие в очередь, для отправки ответа В ЕТП
        при этом сам статус меняем на "A", т.к. у нас в системе статус "A1" не предусмотрен.
    */
    IF OLD.status != NEW.status AND (EXISTS (SELECT 1 FROM etp.message WHERE visit_id = NEW.id)) THEN
        CASE
            WHEN NEW.status = 'C' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
                
            WHEN NEW.status = 'C1' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
                UPDATE visits SET status = 'C' WHERE id = NEW.id;
                NEW.status = 'C';
                                                
            WHEN NEW.status = 'W' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
                
            WHEN NEW.status = 'A' AND OLD.status != 'A1' THEN -- проверка OLD.status != 'A1' для того чтобы не генерировать повторное событие в очереди
                EXECUTE public.push_queue(NEW.id, NEW.status);
                
            WHEN NEW.status = 'A1' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
                UPDATE visits SET status = 'A' WHERE id = NEW.id;
                NEW.status = 'A';                
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.handle_visit_status()
  RETURNS trigger AS
\$BODY$
BEGIN
    /*
        Статус "A1" - изменение со стороны ЕТП. В этом случае генерим событие в очередь, для отправки ответа В ЕТП
        при этом сам статус меняем на "A", т.к. у нас в системе статус "C1" не предусмотрен.
    */
    IF OLD.status != NEW.status AND (EXISTS (SELECT 1 FROM etp.message WHERE visit_id = NEW.id)) THEN
        CASE
            WHEN NEW.status = 'C' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
            WHEN NEW.status = 'W' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
            WHEN NEW.status = 'A' AND OLD.status != 'A1' THEN -- проверка OLD.status != 'A1' для того чтобы не генерировать повторное событие в очереди
                EXECUTE public.push_queue(NEW.id, NEW.status);
            WHEN NEW.status = 'A1' THEN
                EXECUTE public.push_queue(NEW.id, NEW.status);
                UPDATE visits SET status = 'A' WHERE id = NEW.id;
                NEW.status = 'A';                
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
    }
}
