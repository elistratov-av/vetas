<?php

use app\commands\migrate\Migration;

/**
 * Class m200428_062925_create_table_pet_identification_transfer_log
 */
class m200428_062925_create_table_pet_identification_transfer_log extends Migration
{
    private $tableName = 'pet_identification_transfer_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'id_pet_from' => $this->integer(),
            'id_pet_to' => $this->integer(),
            'id_ident_type' => $this->integer(),
            'identification_data' => $this->json(),
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
