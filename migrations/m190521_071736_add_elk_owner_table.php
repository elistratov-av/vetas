<?php

use app\commands\migrate\Migration;

/**
 * Class m190521_071736_add_elk_owner_table
 */
class m190521_071736_add_elk_owner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elk.owners', [
            'id' => $this->primaryKey(),
            'sso_id' => $this->string(255),
            'id_owner' => $this->integer(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'middle_name' => $this->string(),
            'phone' => $this->string(),
            'email' => $this->string(),
            'snils' => $this->string(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk-elk_owners_pet_owners',
            'elk.owners',
            'id_owner',
            'public.pet_owners',
            'id',
            'CASCADE'
        );

        $this->addColumn('elk.pets', 'id_elk_owner', $this->integer()->defaultValue(null));
        $this->addColumn('elk.pets', 'id_pet_owner', $this->integer()->defaultValue(null));
        $this->addColumn('elk.pets', 'id_species', $this->integer());
        $this->addColumn('elk.pets', 'id_breed', $this->integer());
        $this->addColumn('elk.pets', 'name', $this->string());
        $this->addColumn('elk.pets', 'chip', $this->string());
        $this->addColumn('elk.pets', 'birthday', $this->date());
        $this->addColumn('elk.pets', 'sex', $this->boolean());
        $this->addColumn('elk.pets', 'created_at', $this->timestamp(0));
        $this->addColumn('elk.pets', 'updated_at', $this->timestamp(0));

        $this->addForeignKey(
            'fk-elk_pets-id_elk_owner',
            'elk.pets',
            'id_elk_owner',
            'elk.owners',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-elk_pets-id_pet_owner',
            'elk.pets',
            'id_pet_owner',
            'public.pet_owners',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-elk_pets-id_species',
            'elk.pets',
            'id_species',
            'public.species',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-elk_pets-id_breed',
            'elk.pets',
            'id_breed',
            'public.breeds',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-elk_pets-id_elk_owner', 'elk.pets');
        $this->dropForeignKey('fk-elk_pets-id_pet_owner', 'elk.pets');
        $this->dropForeignKey('fk-elk_pets-id_species', 'elk.pets');
        $this->dropForeignKey('fk-elk_pets-id_breed', 'elk.pets');

        $this->dropColumn('elk.pets', 'id_elk_owner');
        $this->dropColumn('elk.pets', 'id_pet_owner');
        $this->dropColumn('elk.pets', 'id_species');
        $this->dropColumn('elk.pets', 'id_breed');
        $this->dropColumn('elk.pets', 'name');
        $this->dropColumn('elk.pets', 'chip');
        $this->dropColumn('elk.pets', 'birthday');
        $this->dropColumn('elk.pets', 'sex');
        $this->dropColumn('elk.pets', 'created_at');
        $this->dropColumn('elk.pets', 'updated_at');

        $this->dropForeignKey('fk-elk_owners_pet_owners', 'elk.owners');
        $this->dropTable('elk.owners');
    }

}
