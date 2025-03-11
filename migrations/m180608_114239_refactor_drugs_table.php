<?php

use yii\db\Migration;

/**
 * Class m180608_114239_refactor_drugs_table
 */
class m180608_114239_refactor_drugs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('drugs');

        $sql = <<<SQL
CREATE TABLE drugs
(
  id_manufactured integer,
  id_representation character varying(255),
  id_measure integer,
  id_active_substance integer,
  id_active_substance_measure integer,
  id_file_packaging_image integer,
  form character varying(255),
  form_description character varying(255),
  unit integer,
  active_substance_unit integer,
  excipients character varying(255),
  packaging character varying(255),
  basis character varying(255),
  CONSTRAINT drugs_pkey PRIMARY KEY (id)
) INHERITS (tmc)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON TABLE drugs IS 'Препараты'");
        $this->execute("COMMENT ON COLUMN drugs.id_manufactured IS 'Ссылка на справочник производителей'");
        $this->execute("COMMENT ON COLUMN drugs.id_representation IS 'Ссылка на справочник представителей'");
        $this->execute("COMMENT ON COLUMN drugs.id_measure IS 'Ссылка на справочник единиц измерений'");
        $this->execute("COMMENT ON COLUMN drugs.id_active_substance IS 'Ссылка на справочник Активных веществ'");
        $this->execute("COMMENT ON COLUMN drugs.id_active_substance_measure IS 'Ссылка на справочник единиц измерений'");
        $this->execute("COMMENT ON COLUMN drugs.id_file_packaging_image IS 'Ссылка на реестр файлов'");
        $this->execute("COMMENT ON COLUMN drugs.form IS 'Лекарственная форма'");
        $this->execute("COMMENT ON COLUMN drugs.form_description IS 'Описание лекарственной формы'");
        $this->execute("COMMENT ON COLUMN drugs.excipients IS 'Вспомогательные вещества'");
        $this->execute("COMMENT ON COLUMN drugs.packaging IS 'Описанеие упаковки'");
        $this->execute("COMMENT ON COLUMN drugs.basis IS 'Основание описание препарата'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('drugs');

        $this->createTable('drugs', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'id_tmc_type' => $this->integer(),
            'id_manufactured' => $this->integer(),
            'id_representation' => $this->string(255),
            'form' => $this->string(255)->notNull(),
            'form_description' => $this->string(255),
            'unit' => $this->integer(),
            'id_measure' => $this->integer(),
            'id_active_substance' => $this->integer(),
            'active_substance_unit' => $this->integer(),
            'id_active_substance_measure' => $this->integer(),
            'excipients' => $this->string(255),
            'packaging' => $this->string(255),
            'id_file_packaging_image' => $this->integer(),
            'basis' => $this->string(255),
        ]);

        $this->createIndex('drug-idx', 'drugs', 'id', true);

        $this->addForeignKey('fk-drugs-id_tmc_type',  'drugs', 'id_tmc_type', 'tmc_types', 'id', 'NO ACTION' );
    }

}
