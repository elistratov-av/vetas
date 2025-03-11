<?php

use app\commands\migrate\Migration;

/**
 * Class m200526_054633_create_table_visit_pets_and_brood
 */
class m200526_054633_create_table_visit_pets_and_brood extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'variety', $this->string());
        $this->addColumn('pets', 'id_brood', $this->integer());
        $this->addColumn('visits_gov_services', 'id_pet', $this->integer());

        $this->createTable('visit_pets',[
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer(),
            'id_visit' => $this->integer()
        ]);

        $this->addForeignKey('visit_fk', 'visit_pets', 'id_visit', 'visits', 'id');
        $this->addForeignKey('pet_fk', 'visit_pets', 'id_pet', 'pets', 'id');

        $this->createTable('broods',[
            'id' => $this->primaryKey(),
            'pet_count' => $this->integer(),
            'id_breeds' => $this->integer(),
            'id_species' => $this->integer(),
            'id_owner' => $this->integer(),
            'birthday' => $this->date(),
            'is_active' => $this->boolean()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pets', 'id_brood');
        $this->dropColumn('visits', 'variety');
        $this->dropColumn('visits_gov_services', 'id_pet');

        $this->dropTable('visit_pets');
        $this->dropTable('broods');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200526_054633_create_table_visit_pets_and_brood cannot be reverted.\n";

        return false;
    }
    */
}
