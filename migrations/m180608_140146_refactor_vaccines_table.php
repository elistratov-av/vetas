<?php

use yii\db\Migration;

/**
 * Class m180608_140146_refactor_vaccines_table
 */
class m180608_140146_refactor_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('vaccines');

        $sql = <<<SQL
CREATE TABLE vaccines
(
  id_manufactured integer,
  id_representation integer,
  id_measure integer,
  id_active_substance integer,
  id_active_substance_measure integer,
  id_file_packaging_image integer,
  form character varying(255) NOT NULL,
  form_description character varying(255),
  unit integer,
  active_substance_unit integer,
  excipients character varying(255),
  packaging character varying(255),
  basis character varying(255),
  CONSTRAINT vaccines_pkey PRIMARY KEY (id)
) INHERITS (tmc)
WITH (
  OIDS=FALSE
);
SQL;
        $this->execute($sql);

        $this->execute("COMMENT ON TABLE vaccines IS 'Вакцины'");
        $this->execute("COMMENT ON COLUMN vaccines.id_manufactured IS 'Ссылка на справочник производителей'");
        $this->execute("COMMENT ON COLUMN vaccines.id_representation IS 'Ссылка на справочник представителей'");
        $this->execute("COMMENT ON COLUMN vaccines.id_measure IS 'Ссылка на справочник единиц измерений'");
        $this->execute("COMMENT ON COLUMN vaccines.id_active_substance IS 'Ссылка на справочник Активных веществ'");
        $this->execute("COMMENT ON COLUMN vaccines.id_active_substance_measure IS 'Ссылка на справочник единиц измерений'");
        $this->execute("COMMENT ON COLUMN vaccines.id_file_packaging_image IS 'Ссылка на реестр файлов'");
        $this->execute("COMMENT ON COLUMN vaccines.form IS 'Лекарственная форма'");
        $this->execute("COMMENT ON COLUMN vaccines.form_description IS 'Описание лекарственной формы'");
        $this->execute("COMMENT ON COLUMN vaccines.excipients IS 'Вспомогательные вещества'");
        $this->execute("COMMENT ON COLUMN vaccines.packaging IS 'Описанеие упаковки'");
        $this->execute("COMMENT ON COLUMN vaccines.basis IS 'Основание описание препарата'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('vaccines', [
                'id' => $this->primaryKey(),
                'id_tmc_type' => $this->integer()->comment('Ссылка на справочник Типов ТМЦ'),
                'id_manufactured' => $this->integer()->comment('Ссылка на справочник производителей'),
                'id_representation' => $this->integer()->comment('Ссылка на справочник представителей'),
                'id_measure' => $this->integer()->comment('Ссылка на справочник единиц измерений'),
                'id_active_substance' => $this->integer()->comment('Ссылка на справочник Активных веществ'),
                'id_active_substance_measure' => $this->integer()->comment('Ссылка на справочник единиц измерений'),
                'id_file_packaging_image' => $this->integer()->comment('Ссылка на реестр файлов'),
                'name' => $this->string(255)->unique()->notNull()->comment('Название'),
                'form' => $this->string(255)->notNull()->comment('Лекарственная форма'),
                'form_description' => $this->string(255)->comment('Описание лекарственной формы'),
                'unit' => $this->string(255)->comment('Содержание активных веществ'),
                'active_substance_unit' => $this->string(255)
                    ->comment('Количество активного вещества в указанном объеме препарата'),
                'excipients' => $this->string(255)->comment('Вспомогательные вещества'),
                'packaging' => $this->text()->comment('Описанеие упаковки'),
                'basis' => $this->text()->comment('Основание описание препарата')
            ]
        );

        $this->addForeignKey(
            'fk--id_tmc_type',
            'vaccines',
            'id_tmc_type',
            'tmc_types',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
    }
}
