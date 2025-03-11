<?php

use yii\db\Migration;

/**
 * Handles the creation of table `owners`.
 */
class m180628_151720_create_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('owners', [
            'id' => $this->primaryKey(),
            'id_address' => $this->integer()->comment('Ссылка на адрес'),
            'f_fio' => $this->string(150)->notNull()->comment('Фамилия'),
            'i_fio' => $this->string(50)->notNull()->comment('Имя'),
            'o_fio' => $this->string(50)->comment('Отчество')
        ]);
        $this->addCommentOnTable('owners', 'Владельцы животных');

        $this->createIndex(
            'ids-owners-fio',
            'owners',
            ['f_fio', 'i_fio', 'o_fio'],
            false
        );

        $this->addForeignKey(
            'fk-owners-id_address',
            'owners',
            'id_address',
            'addresses',
            'id',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('ids-owners-fio', 'owners');
        $this->dropForeignKey('fk-owners-id_address', 'owners');
        $this->dropTable('owners');
    }
}
