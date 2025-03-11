<?php

use app\commands\migrate\Migration;

/**
 * Class m231114_070744_add_desease_to_description_types
 */
class m231114_070744_add_desease_to_description_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('public.description_types', [
            'name' => 'Заболевание',
            'entity_type' => 'visit',
            'tech_name' => 'DISEASE_NAME',
            // Добавьте другие поля, если они не могут быть NULL
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('description_types', ['name' => 'Заболевание']);
//        echo "m231114_070744_add_desease_to_description_types cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231114_070744_add_desease_to_description_types cannot be reverted.\n";

        return false;
    }
    */
}
