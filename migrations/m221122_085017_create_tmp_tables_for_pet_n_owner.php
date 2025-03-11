<?php

use app\commands\migrate\Migration;

/**
 * Class m221122_085017_create_tmp_tables_for_pet_n_owner
 */
class m221122_085017_create_tmp_tables_for_pet_n_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'is_for_unauth_client', $this->boolean()->notNull()->defaultValue(false));

        $this->createTable('pet_owners_tmp', [
            'id' => $this->primaryKey(),
            'f_fio' => $this->string(150)->notNull()->comment('Фамилия'),
            'i_fio' => $this->string(50)->notNull()->comment('Имя'),
            'o_fio' => $this->string(50)->comment('Отчество'),
            'birthday' => $this->date()->comment('Дата рождения'),
            'snils' => $this->string(11)->comment('СНИЛС'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
        ]);
        $this->addCommentOnTable('pet_owners_tmp', 'Данные неавторизованных владельцев животных (заявки mos.ru)');

        // Добавим FK в pet_owners как флаг и чтобы проще найти добавленные временные записи
        $this->addColumn('pet_owners', 'id_pet_owner_tmp', $this->integer());
        $this->addForeignKey(
            'fk-pet_owners-id_owner_tmp',
            'pet_owners',
            'id_pet_owner_tmp',
            'pet_owners_tmp',
            'id',
            'NO ACTION'
        );

        $this->createTable('pets_tmp', [
            'id' => $this->primaryKey(),
            'birthday' => $this->date(),
            'name' => $this->string(50),
            'sex' => $this->string(1),
            'id_species' => $this->integer(),
            'id_breed' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
        ]);
        $this->addCommentOnTable('pets_tmp', 'Данные питомцев неавторизованных пользователей (заявки mos.ru)');

        // Добавим FK в pets как флаг и чтобы проще найти добавленные временные записи
        $this->addColumn('pets', 'id_pet_tmp', $this->integer());
        $this->addForeignKey(
            'fk-pets-id_pet_tmp',
            'pets',
            'id_pet_tmp',
            'pets_tmp',
            'id',
            'NO ACTION'
        );

        // Связка
        $this->createTable('pets_to_owner_tmp', [
            'id' => $this->primaryKey(),
            'id_owner_tmp' => $this->integer()->notNull(),
            'id_pet_tmp' => $this->integer()->notNull(),
            'id_owner_type' => $this->integer()->notNull()
        ]);
        $this->addCommentOnTable('pets_to_owner_tmp', 'pets_tmp - n:m - pet_owners_tmp');

        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_owner_tmp',
            'pets_to_owner_tmp',
            'id_owner_tmp',
            'pet_owners_tmp',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_pet_tmp',
            'pets_to_owner_tmp',
            'id_pet_tmp',
            'pets_tmp',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_owner_type',
            'pets_to_owner_tmp',
            'id_owner_type',
            'pet_owner_type',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            'uniq_pets_to_owner_tmp_id_owner_tmp_id_pet_tmp',
            'pets_to_owner_tmp',
            ['id_pet_tmp', 'id_owner_tmp'],
            TRUE
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pet_owners-id_owner_tmp', 'pet_owners');
        $this->dropForeignKey('fk-pets-id_pet_tmp', 'pets');

        $this->dropTable('pets_to_owner_tmp');
        $this->dropTable('pet_owners_tmp');
        $this->dropTable('pets_tmp');
        $this->dropColumn('visits', 'is_for_unauth_client');

        return true;
    }
}
