<?php

use app\commands\migrate\Migration;

/**
 * Class m210203_081706_create_table_pet_owner_duplicate_log
 */
class m210203_081706_create_table_pet_owner_duplicate_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.pet_owners_link_history', [
            'id' => $this->primaryKey(),
            'id_owner_main' => $this->integer(),
            'id_owner_duplicate' => $this->integer(),
            'values' => $this->json(),
            'created_at' => $this->timestamp()->defaultValue('NOW()'),
            'updated_at' => $this->timestamp()->defaultValue('NOW()'),
            'enabled' => $this->boolean()->defaultValue(true),
        ]);

        $this->addCommentOnTable('public.pet_owners_link_history', 'История слияния владельцев животных');
        $this->addCommentOnColumn('public.pet_owners_link_history','values', 'новые значения полей');

        $this->createIndex('idx_pet_owners_link_history_id_owner_main', 'pet_owners_link_history','id_owner_main');
        $this->createIndex('idx_pet_owners_link_history_id_owner_duplicate', 'pet_owners_link_history','id_owner_duplicate');

        $this->addForeignKey('fk_pet_owners_link_history_id_owner_main', 'pet_owners_link_history',
            'id_owner_main', 'pet_owners', 'id', 'CASCADE');
        $this->addForeignKey('fk_pet_owners_link_history_id_owner_duplicate', 'pet_owners_link_history',
            'id_owner_duplicate', 'pet_owners', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.pet_owners_link_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210203_081706_create_table_pet_owner_duplicate_log cannot be reverted.\n";

        return false;
    }
    */
}
