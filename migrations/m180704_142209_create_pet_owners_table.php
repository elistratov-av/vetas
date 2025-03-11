<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_owners`.
 * Has foreign keys to the tables:
 *
 * - `addresses`
 */
class m180704_142209_create_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_owners', [
            'id' => $this->primaryKey(),
            'f_fio' => $this->string(150)->notNull()->comment('Фамилия'),
            'i_fio' => $this->string(50)->notNull()->comment('Имя'),
            'o_fio' => $this->string(50)->comment('Отчество'),
            'jur_name' => $this->string(150)->comment('Название юр.лица'),
            'inn' => $this->string(12)->comment('ИНН'),
            'ogrn' => $this->string(13)->comment('ОГРН'),
            'birthday' => $this->date()->comment('Дата рождения'),
            'snils' => $this->string(11)->comment('СНИЛС'),
            'id_address' => $this->integer()->comment('Ссылка на адрес'),
        ]);
        $this->addCommentOnTable('pet_owners', 'Владельцы животных');

        // creates index for column `id_address`
        $this->createIndex(
            'idx-pet_owners-id_address',
            'pet_owners',
            'id_address'
        );

        // add foreign key for table `addresses`
        $this->addForeignKey(
            'fk-pet_owners-id_address',
            'pet_owners',
            'id_address',
            'addresses',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `addresses`
        $this->dropForeignKey(
            'fk-pet_owners-id_address',
            'pet_owners'
        );

        // drops index for column `id_address`
        $this->dropIndex(
            'idx-pet_owners-id_address',
            'pet_owners'
        );

        $this->dropTable('pet_owners');
    }
}
