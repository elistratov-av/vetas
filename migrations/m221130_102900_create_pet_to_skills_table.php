<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pets_skill`.
 */
class m221130_102900_create_pet_to_skills_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_to_skills', [
            'id'       => $this->primaryKey(),
            'id_pet'   => $this->integer(),
            'id_skill' => $this->integer()
        ]);

        $this->createIndex(
            'id_pet-idx',
            'pet_to_skills',
            'id_pet'
        );

        $this->createIndex(
            'id_skill-idx',
            'pet_to_skills',
            'id_skill'
        );

        $this->addForeignKey(
            'fk-pet_to_skills-id_pet',
            'pet_to_skills',
            'id_pet',
            'pets',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-pet_to_skills-pet_ref_skill',
            'pet_to_skills',
            'id_skill',
            'pet_ref_skill',
            'id',
            'NO ACTION'
        );

        $this->addCommentOnTable('pet_to_skills', 'Связь животные-навыки');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('pets_skill');
    }
}
