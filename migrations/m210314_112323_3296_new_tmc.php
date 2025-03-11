<?php

use app\commands\migrate\Migration;

/**
 * Class m210314_112323_3296_new_tmc
 */
class m210314_112323_3296_new_tmc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA tmc;');
        $this->execute("COMMENT ON SCHEMA tmc IS 'Товарно-материальная ценности'");

        $this->execute('ALTER TYPE tmc_class_list SET schema "tmc"');

        $this->createTable('tmc.tmc', [
            'id' => $this->primaryKey(),
            'type' => 'tmc.tmc_class_list NOT NULL',
            'name' => $this->text()->notNull(),
            'basis' => $this->text(),
            'dealer' => $this->string(255),
            'description' => $this->string(255),
            'excipients' => $this->text(),
            'form_description' => $this->text(),
            'id_measure' => $this->integer(),
            'unit' => $this->double(2),
            'packaging' => $this->text(),
            'produced' => $this->string(255),
            'registered' => $this->string(255),
            'is_deleted' => $this->boolean()->defaultValue('false')->notNull(),

            'created_at' => $this->timestamp(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->timestamp(0),
            'updated_by' => $this->integer(),
        ]);


        $this->addCommentOnTable('tmc.tmc', 'Справочник тмц');

        $this->addCommentOnColumn('tmc.tmc', 'id', 'id');
        $this->addCommentOnColumn('tmc.tmc', 'type', 'ТИП');
        $this->addCommentOnColumn('tmc.tmc', 'name', 'Наименование');
        $this->addCommentOnColumn('tmc.tmc', 'basis', 'Основание препарата');
        $this->addCommentOnColumn('tmc.tmc', 'dealer', 'Представительство');
        $this->addCommentOnColumn('tmc.tmc', 'description', 'Описание');
        $this->addCommentOnColumn('tmc.tmc', 'excipients', 'Вспомогательные вещества');
        $this->addCommentOnColumn('tmc.tmc', 'form_description', 'Описание лекарственной формы');
        $this->addCommentOnColumn('tmc.tmc', 'id_measure', 'Ссылка на справочник единиц измерений');
        $this->addCommentOnColumn('tmc.tmc', 'packaging', 'Упаковка');
        $this->addCommentOnColumn('tmc.tmc', 'produced', 'Произведено');
        $this->addCommentOnColumn('tmc.tmc', 'registered', 'Зарегистрировано');
        $this->addCommentOnColumn('tmc.tmc', 'unit', 'Содержание активных веществ на ...');
        $this->addCommentOnColumn('tmc.tmc', 'is_deleted', 'Удалено');
        $this->addCommentOnColumn('tmc.tmc', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('tmc.tmc', 'created_by', 'Автор добавления');
        $this->addCommentOnColumn('tmc.tmc', 'updated_at', 'Дата изменения');
        $this->addCommentOnColumn('tmc.tmc', 'updated_by', 'Автор последнего изменения');

        $this->createIndex(
            'tmc_tmc_idx',
            'tmc.tmc',
            ['id', 'type'], true
        );

        $this->execute("
ALTER TABLE tmc.tmc 
  ADD CONSTRAINT produced_check 
    CHECK (
        type NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
            OR 
        (type IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list) AND  produced IS NOT NULL)
        )");

        $this->execute("
ALTER TABLE tmc.tmc 
  ADD CONSTRAINT registered_check 
    CHECK (
        type NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
            OR 
        (type IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list) AND  registered IS NOT NULL)
        )");

        //  ПЕРЕНОС
        $this->copyTMC_to_new_table();

        // Fix Seq

        $this->execute("LOCK TABLE tmc.tmc IN EXCLUSIVE MODE;");
        $this->execute(
            "SELECT setval('tmc.tmc_id_seq', COALESCE((SELECT MAX(id)+1 FROM tmc.tmc), 1), false);"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TYPE tmc.tmc_class_list SET schema "public"');
        $this->dropTable('tmc.tmc');
        $this->execute('DROP SCHEMA tmc;');
    }


    protected function copyTMC_to_new_table()
    {
        $sql = "
INSERT INTO tmc.tmc 
SELECT id,
       'drug'::tmc.tmc_class_list as type,
       name,
       basis,
       dealer,
       NULL AS description,
       excipients,
       form_description,
       id_measure,
       unit,
       packaging,
       registered,
       produced,
       FALSE AS is_deleted,
       created_at,
       created_by,
       updated_at,
       updated_by
FROM drugs
UNION ALL
SELECT id,
       'vaccine'::tmc.tmc_class_list as type,
       name,
       NULL AS basis,
       dealer,
       NULL AS description,
       NULL AS excipients,
       form_description,
       id_measure,
       NULL AS unit,
       packaging,
       registered,
       produced,
       FALSE AS is_deleted,
       created_at,
       created_by,
       updated_at,
       updated_by
FROM vaccines
UNION ALL
SELECT id,
       'exp_material'::tmc.tmc_class_list as type,
       name,
       NULL AS basis,
       NULL AS dealer,
       description,
       NULL AS excipients,
       NULL AS form_description,
       id_measure::int,
       NULL AS unit,
       NULL AS packaging,
       NULL AS registered,
       NULL AS produced,
       FALSE AS is_deleted,
       created_at,
       created_by,
       updated_at,
       updated_by
FROM exp_materials
UNION ALL
SELECT id,
       'equipment'::tmc.tmc_class_list as type,
       name,
       NULL AS basis,
       NULL AS dealer,
       description,
       NULL AS excipients,
       NULL AS form_description,
       NULL::int AS id_measure,
      NULL AS unit,
       NULL AS packaging,
       NULL AS registered,
       NULL AS produced,
       FALSE AS is_deleted,
       created_at,
       created_by,
       updated_at,
       updated_by

FROM equipments
;";
        $this->execute($sql);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210314_112323_3296_new_tmc cannot be reverted.\n";

        return false;
    }
    */
}
