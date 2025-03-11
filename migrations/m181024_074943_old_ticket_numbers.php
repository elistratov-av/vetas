<?php

use app\commands\migrate\Migration;

/**
 * Class m181024_074943_old_ticket_numbers
 */
class m181024_074943_old_ticket_numbers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visits_history', [
            'id' => $this->primaryKey(),
            'id_visit' => $this->integer(),
            'ticket_number' => $this->string(),
            'created_at' => $this->dateTime()->notNull()->defaultValue(new \yii\db\Expression("now()::timestamp without time zone"))
        ]);

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.visits_save_history()
  RETURNS trigger AS
\$BODY$
BEGIN
    INSERT INTO visits_history(id_visit, ticket_number) VALUES (OLD.id, OLD.ticket_number);
    RETURN NEW;
END;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER visits_history_trigger
  AFTER UPDATE OF ticket_number
  ON public.visits
  FOR EACH ROW
  EXECUTE PROCEDURE public.visits_save_history();
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('visits_history');
        $this->execute("DROP TRIGGER visits_history_trigger ON public.visits;");
        $this->execute("DROP FUNCTION public.visits_save_history();");
    }


}
