<?php

use app\commands\migrate\Migration;

/**
 * Class m190315_111511_add_tables_vaccinations
 */
class m190315_111511_add_tables_vaccinations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         *  pet_rabies_vaccination
         */
        $this->createTable('pet_rabies_vaccination', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'id_vaccine' => $this->integer()->notNull(),
            'drug_name' => $this->string(255)->notNull(),
            'producer_name' => $this->string(255)->notNull(),
            'batch' => $this->string(255)->notNull(),
            'production_date' => $this->date(),
            'expiry_date' => $this->date()->notNull(),
            'date' => $this->date()->notNull(),
            'valid_until' => $this->date(),

            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'pet_rabies_vaccination',
            'Вакцинации против бешенства'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'drug_name',
            'Наименование вакцины'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'producer_name',
            'Производитель'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'batch',
            'Номер партии/серии'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'production_date',
            'Дата изготовления'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'expiry_date',
            'Срок годности'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'date',
            'Дата вакцинации'
        );

        $this->addCommentOnColumn(
            'pet_rabies_vaccination',
            'valid_until',
            'Действительно до'
        );

        $this->addForeignKey(
            'fk-pet_rabies_vaccination-pets',
            'pet_rabies_vaccination',
            'id_pet',
            'pets',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_rabies_vaccination-organization',
            'pet_rabies_vaccination',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_rabies_vaccination-specialists',
            'pet_rabies_vaccination',
            'id_specialist',
            'specialists',
            'id',
            'NO ACTION',
            'CASCADE'
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

        $this->createIndex(
            'idx-pet_rabies_vaccination-id_pet',
            'pet_rabies_vaccination',
            'id_pet'
        );

        /*
         *  pet_other_vaccinations
         */
        $this->createTable('pet_other_vaccinations', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'id_vaccine' => $this->integer()->notNull(),
            'drug_name' => $this->string(255)->notNull(),
            'producer_name' => $this->string(255)->notNull(),
            'batch' => $this->string(255)->notNull(),
            'production_date' => $this->date(),
            'expiry_date' => $this->date()->notNull(),
            'date' => $this->date()->notNull(),
            'valid_until' => $this->date(),

            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'pet_other_vaccinations',
            'Другие вакцинации'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'drug_name',
            'Наименование вакцины'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'producer_name',
            'Производитель'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'batch',
            'Номер партии/серии'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'production_date',
            'Дата изготовления'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'expiry_date',
            'Срок годности'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'date',
            'Дата вакцинации'
        );

        $this->addCommentOnColumn(
            'pet_other_vaccinations',
            'valid_until',
            'Действительно до'
        );

        $this->addForeignKey(
            'fk-pet_other_vaccinations-pets',
            'pet_other_vaccinations',
            'id_pet',
            'pets',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_other_vaccinations-organization',
            'pet_other_vaccinations',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_other_vaccinations-specialists',
            'pet_other_vaccinations',
            'id_specialist',
            'specialists',
            'id',
            'NO ACTION',
            'CASCADE'
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

        $this->createIndex(
            'idx-pet_other_vaccinations-id_pet',
            'pet_other_vaccinations',
            'id_pet'
        );

        /*
         * pet_ectoparasites
         */
        $this->createTable('pet_ectoparasites',[
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'id_drug' => $this->integer()->notNull(),
            'drug_name' => $this->string(255)->notNull(),
            'producer_name' => $this->string(255)->notNull(),
            'date' => $this->date()->notNull(),

            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'pet_ectoparasites',
            'Обработки против эктопаразитов'
        );

        $this->addCommentOnColumn(
            'pet_ectoparasites',
            'drug_name',
            'Наименование вакцины'
        );

        $this->addCommentOnColumn(
            'pet_ectoparasites',
            'date',
            'Дата вакцинации'
        );

        $this->addForeignKey(
            'fk-pet_ectoparasites-pets',
            'pet_ectoparasites',
            'id_pet',
            'pets',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_ectoparasites-organization',
            'pet_ectoparasites',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_ectoparasites-specialists',
            'pet_ectoparasites',
            'id_specialist',
            'specialists',
            'id',
            'NO ACTION',
            'CASCADE'
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

        $this->createIndex(
            'idx-pet_ectoparasites-id_pet',
            'pet_ectoparasites',
            'id_pet'
        );

        /*
         * pet_dehelmintization
         */
        $this->createTable('pet_dehelmintization',[
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'id_drug' => $this->integer()->notNull(),
            'drug_name' => $this->string(255)->notNull(),
            'producer_name' => $this->string(255)->notNull(),
            'date' => $this->date()->notNull(),

            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'pet_dehelmintization',
            'Обработки против эктопаразитов'
        );

        $this->addCommentOnColumn(
            'pet_dehelmintization',
            'drug_name',
            'Наименование вакцины'
        );

        $this->addCommentOnColumn(
            'pet_dehelmintization',
            'date',
            'Дата вакцинации'
        );

        $this->addForeignKey(
            'fk-pet_dehelmintization-pets',
            'pet_dehelmintization',
            'id_pet',
            'pets',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_dehelmintization-organization',
            'pet_dehelmintization',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_dehelmintization-specialists',
            'pet_dehelmintization',
            'id_specialist',
            'specialists',
            'id',
            'NO ACTION',
            'CASCADE'
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

        $this->createIndex(
            'idx-pet_dehelmintization-id_pet',
            'pet_dehelmintization',
            'id_pet'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /*
         *  pet_rabies_vaccination
         */
        $this->dropForeignKey(
            'fk-pet_rabies_vaccination-pets',
            'pet_rabies_vaccination'
        );

        $this->dropForeignKey(
            'fk-pet_rabies_vaccination-organization',
            'pet_rabies_vaccination'
        );

        $this->dropForeignKey(
            'fk-pet_rabies_vaccination-specialists',
            'pet_rabies_vaccination'
        );

        $this->dropForeignKey(
            'fk-pet_rabies_vaccination-vaccines',
            'pet_rabies_vaccination'
        );

        $this->dropTable('pet_rabies_vaccination');

        /*
         *  pet_other_vaccinations
         */
        $this->dropForeignKey(
            'fk-pet_other_vaccinations-pets',
            'pet_other_vaccinations'
        );

        $this->dropForeignKey(
            'fk-pet_other_vaccinations-organization',
            'pet_other_vaccinations'
        );

        $this->dropForeignKey(
            'fk-pet_other_vaccinations-specialists',
            'pet_other_vaccinations'
        );

        $this->dropForeignKey(
            'fk-pet_other_vaccinations-vaccines',
            'pet_other_vaccinations'
        );

        $this->dropTable('pet_other_vaccinations');

        /*
         * pet_ectoparasites
         */
        $this->dropForeignKey(
            'fk-pet_ectoparasites-pets',
            'pet_ectoparasites'
        );

        $this->dropForeignKey(
            'fk-pet_ectoparasites-organization',
            'pet_ectoparasites'
        );

        $this->dropForeignKey(
            'fk-pet_ectoparasites-specialists',
            'pet_ectoparasites'
        );

        $this->dropForeignKey(
            'fk-pet_ectoparasites-drugs',
            'pet_ectoparasites'
        );

        $this->dropTable('pet_ectoparasites');

        /*
         * pet_dehelmintization
         */
        $this->dropForeignKey(
            'fk-pet_dehelmintization-pets',
            'pet_dehelmintization'
        );

        $this->dropForeignKey(
            'fk-pet_dehelmintization-organization',
            'pet_dehelmintization'
        );

        $this->dropForeignKey(
            'fk-pet_dehelmintization-specialists',
            'pet_dehelmintization'
        );

        $this->dropForeignKey(
            'fk-pet_dehelmintization-drugs',
            'pet_dehelmintization'
        );

        $this->dropTable('pet_dehelmintization');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190315_111511_add_tables_vaccinations cannot be reverted.\n";

        return false;
    }
    */
}
