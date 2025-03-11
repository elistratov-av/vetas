<?php

use yii\db\Migration;

/**
 * Handles the creation of table `jur_persons`.
 */
class m180628_152319_create_jur_persons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE jur_persons
(
  jur_name character varying(150) NOT NULL,
  inn character varying(12) NOT NULL,  
  ogrn character varying(13) NOT NULL,
  CONSTRAINT jur_persons_pkey PRIMARY KEY (id)
) INHERITS (owners)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON TABLE jur_persons IS 'Владельцы животных: юридические лица'");
        $this->execute("COMMENT ON COLUMN jur_persons.jur_name IS 'Название юр. лица'");
        $this->execute("COMMENT ON COLUMN jur_persons.inn IS 'ИНН'");
        $this->execute("COMMENT ON COLUMN jur_persons.ogrn IS 'ОГРН'");

        $this->createIndex(
            'idx-jur_persons_jur_name',
            'jur_persons',
            'jur_name',
            false
        );

        $this->createIndex(
            'idx-jur_persons-inn',
            'jur_persons',
            'inn',
            false
        );

        $this->createIndex(
            'idx-jur_persons-ogrn',
            'jur_persons',
            'ogrn',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-jur_persons_jur_name', 'jur_persons');
        $this->dropIndex('idx-jur_persons-inn', 'jur_persons');
        $this->dropIndex('idx-jur_persons-ogrn', 'jur_persons');
        $this->dropTable('jur_persons');
    }
}
