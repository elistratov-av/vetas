<?php

use yii\db\Migration;

/**
 * Handles the creation of table `gov_services`.
 */
class m180629_104750_create_gov_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE gov_services
(
  id_service_type integer NOT NULL,
  id_tmc_type integer,
  id_cabinet_type integer,
  id_specialization integer,
  duration integer NOT NULL,
  CONSTRAINT gov_services_pkey PRIMARY KEY (id)
) INHERITS (services)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON TABLE gov_services IS 'Государственные услуги'");
        $this->execute("COMMENT ON COLUMN gov_services.id_service_type IS 'Ссылка на тип услуги'");
        $this->execute("COMMENT ON COLUMN gov_services.id_tmc_type IS 'Ссылка на тип ТМЦ'");
        $this->execute("COMMENT ON COLUMN gov_services.id_cabinet_type IS 'Ссылка на тип кабинета'");
        $this->execute("COMMENT ON COLUMN gov_services.id_specialization IS 'Ссылка на специализацию'");
        $this->execute("COMMENT ON COLUMN gov_services.duration IS 'Длительность'");

        $this->addForeignKey(
            'fk-gov_services-id_service_type',
            'gov_services',
            'id_service_type',
            'service_types',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-gov_services-id_tmc_type',
            'gov_services',
            'id_tmc_type',
            'tmc_types',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-gov_services-id_cabinet_type',
            'gov_services',
            'id_cabinet_type',
            'cabinet_types',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-gov_services-id_specialization',
            'gov_services',
            'id_specialization',
            'specializations',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            'idx-gov_services-id_service_type',
            'gov_services',
            'id_service_type',
            false
        );

        $this->createIndex(
            'idx-gov_services-id_tmc_type',
            'gov_services',
            'id_tmc_type',
            false
        );

        $this->createIndex(
            'idx-gov_services-id_cabinet_type',
            'gov_services',
            'id_cabinet_type',
            false
        );

        $this->createIndex(
            'idx-gov_services-id_specialization',
            'gov_services',
            'id_specialization',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-gov_services-id_service_type', 'gov_services');
        $this->dropForeignKey('fk-gov_services-id_tmc_type', 'gov_services');
        $this->dropForeignKey('fk-gov_services-id_cabinet_type', 'gov_services');
        $this->dropForeignKey('fk-gov_services-id_specialization', 'gov_services');

        $this->dropIndex('idx-gov_services-id_service_type', 'gov_services');
        $this->dropIndex('idx-gov_services-id_tmc_type', 'gov_services');
        $this->dropIndex('idx-gov_services-id_cabinet_type', 'gov_services');
        $this->dropIndex('idx-gov_services-id_specialization', 'gov_services');

        $this->dropTable('gov_services');
    }
}
