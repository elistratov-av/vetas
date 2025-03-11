<?php

use app\commands\migrate\Migration;

/**
 * Class m231121_112301_add_laboratory_file_to_description_types
 */
class m231121_112301_add_laboratory_file_to_description_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('public.description_types', [
            'name' => 'Результаты лабораторных исследований',
            'entity_type' => 'visit',
            'tech_name' => 'LABORATORY_FILE',
            // Добавьте другие поля, если они не могут быть NULL
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('description_types', ['name' => 'Результаты лабораторных исследований']);

//        echo "m231121_112301_add_laboratory_file_to_description_types cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231121_112301_add_laboratory_file_to_description_types cannot be reverted.\n";

        return false;
    }
    */
}
