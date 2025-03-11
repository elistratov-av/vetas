<?php

use app\commands\migrate\Migration;

/**
 * Class m200323_110917_2659_pets_change_field_relocate
 */
class m200323_110917_2659_pets_change_field_relocate extends Migration
{
    private $tableName = 'public.pets';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableName, 'is_relocate');

        $this->addColumn($this->tableName, 'id_relocate', $this->boolean()->comment('Признак переноса записи - ID дублирующей'));

        foreach (['id_relocate'] as $column) {
            $this->createIndex('idx_' . 'pets' . '_' . $column, $this->tableName, $column);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200323_110917_2659_pets_change_field_relocate cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200323_110917_2659_pets_change_field_relocate cannot be reverted.\n";

        return false;
    }
    */
}
