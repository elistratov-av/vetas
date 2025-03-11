<?php

use app\commands\migrate\Migration;

/*

ПОСМОТРЕТЬ КТО ССЫЛАЕТСЯ НА ДАННЫЕ ТАБЛИЦЫ

 select R.constraint_name,R.table_name, R.column_name, '=>' , u.table_name, u.column_name --*
from INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE u
         inner join INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS FK
                    on U.CONSTRAINT_CATALOG = FK.UNIQUE_CONSTRAINT_CATALOG
                        and U.CONSTRAINT_SCHEMA = FK.UNIQUE_CONSTRAINT_SCHEMA
                        and U.CONSTRAINT_NAME = FK.UNIQUE_CONSTRAINT_NAME
         inner join INFORMATION_SCHEMA.KEY_COLUMN_USAGE R
                    ON R.CONSTRAINT_CATALOG = FK.CONSTRAINT_CATALOG
                        AND R.CONSTRAINT_SCHEMA = FK.CONSTRAINT_SCHEMA
                        AND R.CONSTRAINT_NAME = FK.CONSTRAINT_NAME
WHERE
  --U.COLUMN_NAME = 'a' AND
  --U.TABLE_CATALOG = 'b' AND
        U.TABLE_SCHEMA = 'public'
  AND U.TABLE_NAME IN (
           'drugs', 'equipments', 'exp_materials', 'vaccines','tmc',
                      'balance_drugs', 'balance_equipments', 'balance_exp_materials', 'balance_vaccines'
                  );
 */

/**
 * Class m210315_111038_3296_new_tmc_relations
 */
class m210315_111038_3296_new_tmc_relations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        /**
         * TMC - Measure
         * Ранее ключа не было - почистить мусор сначала
         */
        $sql = "UPDATE tmc.tmc SET id_measure=null WHERE tmc.tmc.id_measure NOT IN 
        (SELECT id FROM public.measures);";
        $this->addForeignKey(
            'fk_tmc_tmc-measure',
            'tmc.tmc',
            'id_measure',
            'measures',
            'id'
        );

        /**
         * vaccines_to_species => tmc.tmc_to_species
         * Теперь для препаратов и вакцин
         */
        $this->execute("ALTER TABLE vaccines_to_species SET SCHEMA tmc");
        $this->renameTable('tmc.vaccines_to_species', 'tmc_to_species');
        $this->addCommentOnTable('tmc.tmc_to_species', 'Применение вакцин|препаратов для видов животных');
        $this->dropForeignKey(
            'fk_vaccines_to_species_id_vaccine',
            'tmc.tmc_to_species'
        );
        $this->renameColumn(
            'tmc.tmc_to_species',
            'id_vaccine',
            'id_tmc'
        );
        $this->addColumn(
            'tmc.tmc_to_species',
            'type_tmc',
            "tmc.tmc_class_list"
        );
        $this->execute("
        ALTER TABLE tmc.tmc_to_species 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'drug'::tmc.tmc_class_list OR type_tmc = 'vaccine'::tmc.tmc_class_list)"
        );
        $this->execute("UPDATE tmc.tmc_to_species SET type_tmc = 'vaccine'::tmc.tmc_class_list");
        $this->execute('ALTER TABLE tmc.tmc_to_species ALTER COLUMN type_tmc SET NOT NULL;');
        $this->addForeignKey(
            'fk-tmc_to_species-tmc_tmc',
            'tmc.tmc_to_species',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        /**
         * vaccines_to_diseases => tmc.tmc_to_diseases
         * Теперь для препаратов и вакцин
         */
        $this->execute("ALTER TABLE vaccines_to_diseases SET SCHEMA tmc");
        $this->renameTable('tmc.vaccines_to_diseases', 'tmc_to_diseases');
        $this->addCommentOnTable('tmc.tmc_to_diseases', 'Применение вакцин|препаратов при заболеваниях');
        $this->dropForeignKey(
            'fk_vaccines_to_diseases_id_vaccine',
            'tmc.tmc_to_diseases'
        );
        $this->renameColumn(
            'tmc.tmc_to_diseases',
            'id_vaccine',
            'id_tmc'
        );
        $this->addColumn(
            'tmc.tmc_to_diseases',
            'type_tmc',
            "tmc.tmc_class_list"
        );
        $this->execute("
        ALTER TABLE tmc.tmc_to_diseases 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'drug'::tmc.tmc_class_list OR type_tmc = 'vaccine'::tmc.tmc_class_list)"
        );
        $this->execute("UPDATE tmc.tmc_to_diseases SET type_tmc = 'vaccine'::tmc.tmc_class_list");
        $this->execute('ALTER TABLE tmc.tmc_to_diseases ALTER COLUMN type_tmc SET NOT NULL;');
        $this->addForeignKey(
            'fk-tmc_to_diseases-tmc_tmc',
            'tmc.tmc_to_diseases',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        /**
         * dosages теперь и для вакцин и для препаратов
         */

        $this->execute("ALTER TABLE dosages SET SCHEMA tmc");
        $this->dropForeignKey('fk-dosages-id_drug', 'tmc.dosages');
        $this->renameColumn(
            'tmc.dosages',
            'id_drug',
            'id_tmc'
        );
        $this->addColumn(
            'tmc.dosages',
            'type_tmc',
            "tmc.tmc_class_list"
        );
        $this->execute("
        ALTER TABLE tmc.dosages 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'drug'::tmc.tmc_class_list OR type_tmc = 'vaccine'::tmc.tmc_class_list)"
        );
        $this->execute("UPDATE tmc.dosages SET type_tmc = 'drug'::tmc.tmc_class_list");
        $this->execute('ALTER TABLE tmc.dosages ALTER COLUMN type_tmc SET NOT NULL;');

        $this->addForeignKey(
            'fk-tmc_dosages-tmc_tmc',
            'tmc.dosages',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        /**
         * В "drugs', 'equipments', 'exp_materials', 'vaccines','tmc' был сквозной id и он перешел в tmc.tmc как есть
         * Но теперь в таблице все типы ТМЦ и ссылаться надо на составной ключ,
         * причем ограничивая тип в ссылающейся таблицы только на допустимый
         *
         * Алгоритм:
         * 1. Удаляем старый внешний ключ
         * 2. Добавляем новый столбец тип тмц
         * 3. Добавляем на него ограничение "только нужный тип"
         * 4. Вешаем новый внешний ключ (составной)
         */

        // -----------------------
        //    pet_ectoparasites
        // -----------------------
        $this->dropForeignKey('fk-pet_ectoparasites-drugs', 'pet_ectoparasites');
        $this->addColumn(
            'pet_ectoparasites',
            'type_tmc',
            "tmc.tmc_class_list DEFAULT 'drug'::tmc.tmc_class_list NOT NULL"
        );
        $this->execute("
        ALTER TABLE pet_ectoparasites 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'drug'::tmc.tmc_class_list)"
        );
        $this->addForeignKey(
            'fk-pet_ectoparasites-tmc_tcm-drugs',
            'pet_ectoparasites',
            ['id_drug', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        // -----------------------
        //    pet_dehelmintization
        // -----------------------
        $this->dropForeignKey('fk-pet_dehelmintization-drugs', 'pet_dehelmintization');
        $this->addColumn(
            'pet_dehelmintization',
            'type_tmc',
            "tmc.tmc_class_list DEFAULT 'drug'::tmc.tmc_class_list NOT NULL"
        );
        $this->execute("
        ALTER TABLE pet_dehelmintization 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'drug'::tmc.tmc_class_list)"
        );
        $this->addForeignKey(
            'fk-pet_dehelmintization-tmc_tcm-drugs',
            'pet_dehelmintization',
            ['id_drug', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        // -----------------------
        //    pet_rabies_vaccination
        // -----------------------
        $this->dropForeignKey('fk-pet_rabies_vaccination-vaccines', 'pet_rabies_vaccination');
        $this->addColumn(
            'pet_rabies_vaccination',
            'type_tmc',
            "tmc.tmc_class_list DEFAULT 'vaccine'::tmc.tmc_class_list NOT NULL"
        );
        $this->execute("
        ALTER TABLE pet_rabies_vaccination 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'vaccine'::tmc.tmc_class_list)"
        );
        $this->addForeignKey(
            'fk-pet_rabies_vaccination-tmc_tmc-vaccines',
            'pet_rabies_vaccination',
            ['id_vaccine', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );


        // -----------------------
        //    pet_other_vaccinations
        // -----------------------
        $this->dropForeignKey('fk-pet_other_vaccinations-vaccines', 'pet_other_vaccinations');
        $this->addColumn(
            'pet_other_vaccinations',
            'type_tmc',
            "tmc.tmc_class_list DEFAULT 'vaccine'::tmc.tmc_class_list NOT NULL"
        );
        $this->execute("
        ALTER TABLE pet_other_vaccinations 
        ADD CONSTRAINT type_tmc_check 
            CHECK (type_tmc = 'vaccine'::tmc.tmc_class_list)"
        );
        $this->addForeignKey(
            'fk-pet_other_vaccinations-tmc_tcm-vaccines',
            'pet_other_vaccinations',
            ['id_vaccine', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->addForeignKey(
            'fk_tmc_tmc-measure',
            'tmc.tmc'
        );

        /**
         * vaccines_to_species => tmc.tmc_to_species
         */
        $this->dropForeignKey(
            'fk-tmc_to_species-tmc_tmc',
            'tmc.tmc_to_species'
        );
        $this->renameColumn(
            'tmc.tmc_to_species',
            'id_tmc',
            'id_vaccine'
        );
        $this->dropColumn(
            'tmc.tmc_to_species',
            'type_tmc'
        );
        $this->renameTable('tmc.tmc_to_species', 'vaccines_to_species');
        $this->execute("ALTER TABLE tmc.vaccines_to_species SET SCHEMA public");
        $this->addCommentOnTable('vaccines_to_species', 'Применение вакцины: заболевание');

        $this->addForeignKey(
            'fk_vaccines_to_species_id_vaccine',
            'vaccines_to_species',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /**
         * vaccines_to_diseases => tmc.tmc_to_diseases
         */
        $this->dropForeignKey(
            'fk-tmc_to_diseases-tmc_tmc',
            'tmc.tmc_to_diseases'
        );
        $this->renameColumn(
            'tmc.tmc_to_diseases',
            'id_tmc',
            'id_vaccine'
        );
        $this->dropColumn(
            'tmc.tmc_to_diseases',
            'type_tmc'
        );
        $this->renameTable('tmc.tmc_to_diseases', 'vaccines_to_diseases');
        $this->execute("ALTER TABLE tmc.vaccines_to_diseases SET SCHEMA public");
        $this->addCommentOnTable('vaccines_to_diseases', 'Применение вакцины: заболевание');

        $this->addForeignKey(
            'fk_vaccines_to_diseases_id_vaccine',
            'vaccines_to_diseases',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE',
            'CASCADE'
        );


        /**
         * dosages
         */
        $this->execute("ALTER TABLE tmc.dosages SET SCHEMA public");
        $this->dropForeignKey('fk-tmc_dosages-tmc_tmc', 'dosages');
        $this->renameColumn(
            'dosages',
            'id_tmc',
            'id_drug'
        );
        $this->dropColumn(
            'dosages',
            'type_tmc'
        );
        $this->addForeignKey(
            'fk-dosages-id_drug',
            'dosages',
            'id_drug',
            'drugs',
            'id'
        );

        // -----------------------
        //    pet_ectoparasites
        // -----------------------
        $this->dropForeignKey('fk-pet_ectoparasites-tmc_tcm-drugs', 'pet_ectoparasites');
        $this->dropColumn(
            'pet_ectoparasites',
            'type_tmc'
        );

        $this->addForeignKey(
            'fk-pet_ectoparasites-drugs',
            'pet_ectoparasites',
            'id_drug',
            'drugs',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        // -----------------------
        //    pet_dehelmintization
        // -----------------------
        $this->dropForeignKey('fk-pet_dehelmintization-tmc_tcm-drugs', 'pet_dehelmintization');
        $this->dropColumn(
            'pet_dehelmintization',
            'type_tmc'
        );
        $this->addForeignKey(
            'fk-pet_dehelmintization-drugs',
            'pet_dehelmintization',
            'id_drug',
            'drugs',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        // -----------------------
        //    pet_rabies_vaccination
        // -----------------------
        $this->dropForeignKey('fk-pet_rabies_vaccination-tmc_tmc-vaccines', 'pet_rabies_vaccination');
        $this->dropColumn(
            'pet_rabies_vaccination',
            'type_tmc'
        );
        $this->addForeignKey(
            'fk-pet_rabies_vaccination-vaccines',
            'pet_rabies_vaccination',
            'id_vaccine',
            'vaccines',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        // -----------------------
        //    pet_other_vaccinations
        // -----------------------
        $this->dropForeignKey('fk-pet_other_vaccinations-tmc_tcm-vaccines', 'pet_other_vaccinations');
        $this->dropColumn(
            'pet_other_vaccinations',
            'type_tmc'
        );

        $this->addForeignKey(
            'fk-pet_other_vaccinations-vaccines',
            'pet_other_vaccinations',
            'id_vaccine',
            'vaccines',
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210315_111038_3296_new_tmc_relations cannot be reverted.\n";

        return false;
    }
    */
}
