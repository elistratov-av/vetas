<?php

use yii\db\Migration;

/**
 * Class m180731_155709_mosru_statistic_rating_change
 */
class m180731_155709_mosru_statistic_rating_change extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Удаляем старую таблицу
        $this->execute("DROP TABLE statistic.gov_services_rating;");

        // Создаем новую
        $this->execute('CREATE TABLE statistic.mosru_services_rating
(
  id serial, -- ID
  mosru_services_id integer,
  visits_count integer, -- Кол-во записей
  sort_by integer, -- Сортировка
  CONSTRAINT service_rating_pkey PRIMARY KEY (id),
  CONSTRAINT gov_services_rating_mosru_services_id_fkey FOREIGN KEY (mosru_services_id)
      REFERENCES public.mosru_services (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)');
        $this->execute("COMMENT ON TABLE statistic.mosru_services_rating
  IS 'Рейтинг услуг (для mos.ru)';");

        $this->execute("COMMENT ON COLUMN statistic.mosru_services_rating.id IS 'ID';");
        $this->execute("COMMENT ON COLUMN statistic.mosru_services_rating.visits_count IS 'Кол-во записей';");
        $this->execute("COMMENT ON COLUMN statistic.mosru_services_rating.sort_by IS 'Сортировка';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180731_155709_mosru_statistic_rating_change cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180731_155709_mosru_statistic_rating_change cannot be reverted.\n";

        return false;
    }
    */
}
