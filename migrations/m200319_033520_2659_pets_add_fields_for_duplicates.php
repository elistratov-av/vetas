<?php

use app\commands\migrate\Migration;

/**
 * Class m200319_033520_2659_pets_add_fields_for_duplicates
 */
class m200319_033520_2659_pets_add_fields_for_duplicates extends Migration
{
    private $tableName = 'public.pets';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'is_main', $this->boolean()->comment('Признак основной записи'));
        $this->addColumn($this->tableName, 'id_main_pet', $this->integer()->comment('Ссылка на идентификатор основной записи'));
        $this->addColumn($this->tableName, 'is_relocate', $this->boolean()->comment('Признак переноса записи из дублирующей'));
        $this->addColumn($this->tableName, 'duble_validation', $this->dateTime(0)->comment('Дата проведения проверки на дубли'));

        foreach (['is_main', 'id_main_pet', 'is_relocate'] as $column) {
            $this->createIndex('idx_' . 'pets' . '_' . $column, $this->tableName, $column);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['is_main', 'id_main_pet', 'is_relocate', 'duble_validation'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
