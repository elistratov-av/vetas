<?php

use app\commands\migrate\Migration;

/**
 * Class m210205_070436_create_table_pet_duplicate_link_history
 */
class m210205_070436_create_table_pet_duplicate_link_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.pets_link_history', [
            'id' => $this->primaryKey(),
            'id_pet_main' => $this->integer(),
            'id_pet_duplicate' => $this->integer(),
            'values' => $this->json(),
            'created_at' => $this->timestamp()->defaultValue('NOW()'),
            'updated_at' => $this->timestamp()->defaultValue('NOW()'),
            'enabled' => $this->boolean()->defaultValue(true),
        ]);

        $this->addCommentOnTable('public.pets_link_history', 'История слияния животных');
        $this->addCommentOnColumn('public.pets_link_history','values', 'новые значения полей');

        $this->createIndex('idx_pets_link_history_id_pet_main', 'pets_link_history','id_pet_main');
        $this->createIndex('idx_pets_link_history_id_pet_duplicate', 'pets_link_history','id_pet_duplicate');

        $this->addForeignKey('fk_pets_link_history_id_pet_main', 'pets_link_history',
            'id_pet_main', 'pets', 'id', 'CASCADE');
        $this->addForeignKey('fk_pet_pets_link_history_id_pet_duplicate', 'pets_link_history',
            'id_pet_duplicate', 'pets', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.pets_link_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210205_070436_create_table_pet_duplicate_link_history cannot be reverted.\n";

        return false;
    }
    */
}
