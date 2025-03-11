<?php

use app\commands\migrate\Migration;

/**
 * Class m200708_065147_create_table_pets_to_owner_transfer_log
 */
class m200708_065147_create_table_pets_to_owner_transfer_log extends Migration
{
    private $tableName = 'pets_to_owner_transfer_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'id_pet_from' => $this->integer()->comment('ID животного дубля'),
            'id_pet_to' => $this->integer()->comment('ID основного животного'),
            'links_pet_from' => $this->json()->comment('Представители животного-дубля, которые были до переноса'),
            'links_pet_to' => $this->json()->comment('Представители, которые были перенесены в основное животное'),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        foreach (['id_pet_from', 'id_pet_to'] as $column) {
            $this->createIndex('idx_' . $this->tableName . '_' . $column, $this->tableName, $column);
        }

        $this->addForeignKey(
            'fk_' . $this->tableName . '_' . 'id_pet_from',
            $this->tableName,
            'id_pet_from',
            'public.pets',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_' . $this->tableName . '_' . 'id_pet_to',
            $this->tableName,
            'id_pet_to',
            'public.pets',
            'id',
            'CASCADE'
        );

        $this->addCommentOnTable($this->tableName, 'Склейка - лог переносов представителей от дубля к основному животному');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
