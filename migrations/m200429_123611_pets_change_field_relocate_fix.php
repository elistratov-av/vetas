<?php

use app\commands\migrate\Migration;

/**
 * Class m200429_123611_pets_change_field_relocate_fix
 */
class m200429_123611_pets_change_field_relocate_fix extends Migration
{
    private $tableName = 'public.pets';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableName, 'id_relocate');

        $this->addColumn($this->tableName, 'id_relocate', $this->integer()->comment('Признак переноса записи - ID дублирующей'));

        foreach (['id_relocate'] as $column) {
            $this->createIndex('idx_' . 'pets' . '_' . $column, $this->tableName, $column);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200429_123611_pets_change_field_relocate_fix cannot be reverted.\n";

        return false;
    }
}
