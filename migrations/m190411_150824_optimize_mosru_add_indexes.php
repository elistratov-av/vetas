<?php

use app\commands\migrate\Migration;

/**
 * Class m190411_150824_optimize_mosru_add_indexes
 */
class m190411_150824_optimize_mosru_add_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE INDEX "idx-timesheets-lower_date"
  ON public.timesheets
  USING btree
  (lower(date));
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE INDEX "idx-timesheets-upper_date"
  ON public.timesheets
  USING btree
  (upper(date));
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE INDEX "idx-specialists-user_organization"
  ON public.specialists
  USING btree
  (id_user, id_organization);
SQL;
        $this->execute($sql);

        $sql = <<<SQL
  CREATE INDEX "idx-visits-status"
  ON public.visits
  USING btree
  (status COLLATE pg_catalog."default");
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP INDEX public."idx-timesheets-lower_date"');
        $this->execute('DROP INDEX public."idx-timesheets-upper_date"');
        $this->execute('DROP INDEX public."idx-specialists-user_organization"');
        $this->execute('DROP INDEX public."idx-visits-status"');
    }

}
