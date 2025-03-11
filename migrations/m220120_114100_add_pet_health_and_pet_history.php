<?php

use app\commands\migrate\Migration;

/**
 * Class m220120_114100_add_pet_health_and_pet_history
 */
class m220120_114100_add_pet_health_and_pet_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.pet_health', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'date' => $this->date()->notNull(),
            'temperature' => $this->double(),
            'weight' => $this->double(),
            'anamnesis' => $this->string(),
            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addForeignKey(
            'fk-pet_health-id_pet',
            'pet_health',
            'id_pet',
            'pets',
            'id'
        );
        
        $this->addForeignKey(
            'fk-pet_health-id_organization',
            'pet_health',
            'id_organization',
            'organizations',
            'id'
        );
        
        $this->addForeignKey(
            'fk-pet_health-id_specialist',
            'pet_health',
            'id_specialist',
            'specialists',
            'id'
        );
        
        $this->addForeignKey(
            'fk-pet_health-created_by',
            'pet_health',
            'created_by',
            'users',
            'id'
        );

        $this->addForeignKey(
            'fk-pet_health-updated_by',
            'pet_health',
            'updated_by',
            'users',
            'id'
        );

        $this->createTable('public.pet_history', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'event' => $this->string()->notNull(),
            'options' => $this->integer(),
            'id_organization' => $this->integer()->notNull(),            
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-pet_history-id_pet',
            'pet_history',
            'id_pet',
            'pets',
            'id'
        );        

        $this->addForeignKey(
            'fk-pet_history-id_organization',
            'pet_history',
            'id_organization',
            'organizations',
            'id'
        );

        $this->addForeignKey(
            'fk-pet_history-created_by',
            'pet_history',
            'created_by',
            'users',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pet_health-id_pet', 'pet_health');
        $this->dropForeignKey('fk-pet_health-id_organization', 'pet_health');
        $this->dropForeignKey('fk-pet_health-id_specialist', 'pet_health');
        $this->dropForeignKey('fk-pet_health-created_by', 'pet_health');
        $this->dropForeignKey('fk-pet_health-updated_by', 'pet_health');
        $this->dropTable('public.pet_health');

        
        $this->dropForeignKey('fk-pet_history-id_pet', 'pet_history');
        $this->dropForeignKey('fk-pet_history-id_organization', 'pet_history');
        $this->dropForeignKey('fk-pet_history-created_by', 'pet_history');
        $this->dropTable('public.pet_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220120_114100_add_pet_health_and_pet_history cannot be reverted.\n";

        return false;
    }
    */
}