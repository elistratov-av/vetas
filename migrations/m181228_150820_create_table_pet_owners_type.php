<?php

use app\commands\migrate\Migration;

/**
 * Class m181228_150820_create_table_pet_owners_type
 */
class m181228_150820_create_table_pet_owners_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_owner_type',[
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique()->notNull(),
        ]);

        $this->insert('pet_owner_type',[
            'name' => 'Владелец'
        ]);

        $this->insert('pet_owner_type',[
            'name' => 'Представитель'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('pet_owner_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181228_150820_create_table_pet_owners_type cannot be reverted.\n";

        return false;
    }
    */
}
