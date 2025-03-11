<?php

use yii\db\Migration;

/**
 * Handles the creation of table `nat_persons`.
 */
class m180628_152327_create_nat_persons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE nat_persons
(
  birthday date DEFAULT NULL,  
  snils character varying(11) DEFAULT NULL,
  CONSTRAINT nat_persons_pkey PRIMARY KEY (id)
) INHERITS (owners)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON TABLE nat_persons IS 'Владельцы животных: физические лица'");
        $this->execute("COMMENT ON COLUMN nat_persons.birthday IS 'Дата рождения'");
        $this->execute("COMMENT ON COLUMN nat_persons.snils IS 'СНИЛС'");

        $this->createIndex(
            'idx-nat_persons-snils',
            'nat_persons',
            'snils',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-nat_persons-snils', 'nat_persons');
        $this->dropTable('nat_persons');
    }
}
