<?php

use app\commands\migrate\Migration;

/**
 * Handles the creation of table `breeds_dosages`.
 */
class m190808_044900_create_species_dosages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('species_dosages', [
            'id' => $this->primaryKey(),
            'id_dosage' => $this->integer()->notNull(),
            'id_species' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);


        $this->addForeignKey('fk-species_dosages-id_dosage',
            'species_dosages',
            'id_dosage',
            'dosages',
            'id',
            'CASCADE'
        );


        $this->addForeignKey('fk-species_dosages-id_breed',
            'species_dosages',
            'id_species',
            'species',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('species_dosages');
    }
}
