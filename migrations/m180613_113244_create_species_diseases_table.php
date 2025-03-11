<?php

use yii\db\Migration;

/**
 * Handles the creation of table `species_diseases`.
 * Has foreign keys to the tables:
 *
 * - `diseases`
 * - `species`
 */
class m180613_113244_create_species_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('species_diseases', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'description' => $this->string(),
            'id_disease' => $this->integer(),
            'id_species' => $this->integer(),
        ]);

        // creates index for column `id_disease`
        $this->createIndex(
            'idx-species_diseases-id_disease',
            'species_diseases',
            'id_disease'
        );

        // add foreign key for table `diseases`
        $this->addForeignKey(
            'fk-species_diseases-id_disease',
            'species_diseases',
            'id_disease',
            'diseases',
            'id',
            'CASCADE'
        );

        // creates index for column `id_species`
        $this->createIndex(
            'idx-species_diseases-id_species',
            'species_diseases',
            'id_species'
        );

        // add foreign key for table `species`
        $this->addForeignKey(
            'fk-species_diseases-id_species',
            'species_diseases',
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
        // drops foreign key for table `diseases`
        $this->dropForeignKey(
            'fk-species_diseases-id_disease',
            'species_diseases'
        );

        // drops index for column `id_disease`
        $this->dropIndex(
            'idx-species_diseases-id_disease',
            'species_diseases'
        );

        // drops foreign key for table `species`
        $this->dropForeignKey(
            'fk-species_diseases-id_species',
            'species_diseases'
        );

        // drops index for column `id_species`
        $this->dropIndex(
            'idx-species_diseases-id_species',
            'species_diseases'
        );

        $this->dropTable('species_diseases');
    }
}
