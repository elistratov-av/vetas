<?php

use app\commands\migrate\Migration;

/**
 * Class m200319_033548_2660_pet_owners_add_fields_for_duplicates
 */
class m200319_033548_2660_pet_owners_add_fields_for_duplicates extends Migration
{
    private $tableName = 'public.pet_owners';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'is_main', $this->boolean()->comment('Признак основной записи'));
        $this->addColumn($this->tableName, 'id_main_owner', $this->integer()->comment('Ссылка на идентификатор основной записи'));
        $this->addColumn($this->tableName, 'duble_validation', $this->dateTime(0)->comment('Дата проведения проверки на дубли'));

        foreach (['is_main', 'id_main_owner'] as $column) {
            $this->createIndex('idx_' . 'pet_owners' . '_' . $column, $this->tableName, $column);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['is_main', 'id_main_owner', 'duble_validation'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
