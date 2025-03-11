<?php

use yii\db\Migration;

/**
 * Class m180608_135855_refactor_equipment_table
 */
class m180608_135855_refactor_equipment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('equipments');

        $sql = <<<SQL
CREATE TABLE equipments
(
  description character varying(255),
  CONSTRAINT equipments_pkey PRIMARY KEY (id)
) INHERITS (tmc)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON TABLE equipments IS 'Оборудование'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('equipments');

        $this->createTable('equipments', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'id_tmc_type' => $this->integer(),
            'description' => $this->string(),
        ]);

        $this->createIndex('name-idx', 'equipments', 'name', true);

        $this->addForeignKey('fk-equipments-id_tmc_type',  'equipments', 'id_tmc_type', 'tmc_types', 'id', 'NO ACTION' );
    }
}
