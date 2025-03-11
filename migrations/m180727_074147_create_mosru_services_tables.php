<?php

use yii\db\Migration;

/**
 * Class m180727_074147_create_mosru_services_tables
 */
class m180727_074147_create_mosru_services_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "CREATE TABLE public.mosru_services
    (
        id serial NOT NULL , -- ID
        id_service_goal integer,
        id_service_type integer NOT NULL, -- Ссылка на тип услуги
        name character varying(255) NOT NULL,
        at_home boolean NOT NULL DEFAULT FALSE, -- Услуга может быть доступна для записи на дом
        at_clinic boolean NOT NULL DEFAULT TRUE, -- Услуга может быть оказана в клинике
        CONSTRAINT mosru_services_pkey PRIMARY KEY (id),
        CONSTRAINT mosru_services_id_service_goal_fkey FOREIGN KEY (id_service_goal)
        REFERENCES public.service_goal (id) MATCH SIMPLE
        ON UPDATE NO ACTION ON DELETE NO ACTION
  )";
        $this->execute($sql);

        $this->execute("COMMENT ON TABLE public.mosru_services
  IS 'Справочник услуг для mosru';");

        $this->execute("COMMENT ON COLUMN public.mosru_services.id IS 'ID';");
        $this->execute("COMMENT ON COLUMN public.mosru_services.id_service_type IS 'Ссылка на тип услуги';");
        $this->execute("COMMENT ON COLUMN public.mosru_services.at_home IS 'Услуга может быть доступна для записи на дом';");
        $this->execute("COMMENT ON COLUMN public.mosru_services.at_clinic IS 'Услуга может быть оказана в клинике';");


        $sql_2 = "CREATE TABLE public.mosru_services_gov_services
            (
              id serial, -- ID
              id_mosru_service integer NOT NULL, -- Ссылка на mosru_services
              id_gov_services integer NOT NULL, -- Ссылка на gov_services
              at_home boolean NOT NULL DEFAULT false,
              CONSTRAINT mosru_services_gov_services_pkey PRIMARY KEY (id),
              CONSTRAINT mosru_services_gov_services_id_gov_services_fkey FOREIGN KEY (id_gov_services)
                  REFERENCES public.gov_services (id) MATCH SIMPLE
                  ON UPDATE NO ACTION ON DELETE NO ACTION,
              CONSTRAINT mosru_services_gov_services_id_mosru_service_fkey FOREIGN KEY (id_mosru_service)
                  REFERENCES public.mosru_services (id) MATCH SIMPLE
                  ON UPDATE NO ACTION ON DELETE NO ACTION
            );
        ";

        $this->execute($sql_2);

        $this->execute("COMMENT ON TABLE public.mosru_services_gov_services
  IS 'Связь mosru_services с gov_services';");
        $this->execute("COMMENT ON COLUMN public.mosru_services_gov_services.id IS 'ID';");
        $this->execute("COMMENT ON COLUMN public.mosru_services_gov_services.id_mosru_service IS 'Ссылка на mosru_services';");
        $this->execute("COMMENT ON COLUMN public.mosru_services_gov_services.id_gov_services IS 'Ссылка на gov_services';");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TABLE public.mosru_services_gov_services;");
        $this->execute("DROP TABLE public.mosru_services;");
    }

}
