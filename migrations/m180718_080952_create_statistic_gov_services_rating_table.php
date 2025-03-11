w<?php

use yii\db\Migration;

/**
 * Handles the creation of table `statistic_gov_services_rating`.
 */
class m180718_080952_create_statistic_gov_services_rating_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE TABLE statistic.gov_services_rating
(
  id serial, -- ID
  gov_service_id integer,
  visits_count integer, -- Кол-во записей
  sort_by integer, -- Сортировка
  CONSTRAINT service_rating_pkey PRIMARY KEY (id),
  CONSTRAINT gov_services_rating_gov_service_id_fkey FOREIGN KEY (gov_service_id)
      REFERENCES public.gov_services (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)');
        $this->execute("COMMENT ON TABLE statistic.gov_services_rating
  IS 'Рейтинг услуг (для mos.ru)';");

        $this->execute("COMMENT ON COLUMN statistic.gov_services_rating.id IS 'ID';");
        $this->execute("COMMENT ON COLUMN statistic.gov_services_rating.visits_count IS 'Кол-во записей';");
        $this->execute("COMMENT ON COLUMN statistic.gov_services_rating.sort_by IS 'Сортировка';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TABLE statistic.gov_services_rating;");
    }
}
