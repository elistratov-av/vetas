<?php

use yii\db\Migration;

/**
 * Handles the creation of table `vaccine_uses`.
 * Has foreign keys to the tables:
 *
 * - `vaccines`
 * - `species`
 * - `diseases`
 */
class m180808_214124_create_vaccine_uses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('vaccine_uses', [
            'id' => $this->primaryKey(),
            'validity' => $this->integer()->notNull(),
            'id_vaccine' => $this->integer(),
            'id_species' => $this->integer(),
            'id_disease' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
        ]);

        // creates index for column `id_vaccine`
        $this->createIndex(
            'idx-vaccine_uses-id_vaccine',
            'vaccine_uses',
            'id_vaccine'
        );

        // add foreign key for table `vaccines`
        $this->addForeignKey(
            'fk-vaccine_uses-id_vaccine',
            'vaccine_uses',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE'
        );

        // creates index for column `id_species`
        $this->createIndex(
            'idx-vaccine_uses-id_species',
            'vaccine_uses',
            'id_species'
        );

        // add foreign key for table `species`
        $this->addForeignKey(
            'fk-vaccine_uses-id_species',
            'vaccine_uses',
            'id_species',
            'species',
            'id',
            'CASCADE'
        );

        // creates index for column `id_disease`
        $this->createIndex(
            'idx-vaccine_uses-id_disease',
            'vaccine_uses',
            'id_disease'
        );

        // add foreign key for table `diseases`
        $this->addForeignKey(
            'fk-vaccine_uses-id_disease',
            'vaccine_uses',
            'id_disease',
            'diseases',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `vaccines`
        $this->dropForeignKey(
            'fk-vaccine_uses-id_vaccine',
            'vaccine_uses'
        );

        // drops index for column `id_vaccine`
        $this->dropIndex(
            'idx-vaccine_uses-id_vaccine',
            'vaccine_uses'
        );

        // drops foreign key for table `species`
        $this->dropForeignKey(
            'fk-vaccine_uses-id_species',
            'vaccine_uses'
        );

        // drops index for column `id_species`
        $this->dropIndex(
            'idx-vaccine_uses-id_species',
            'vaccine_uses'
        );

        // drops foreign key for table `diseases`
        $this->dropForeignKey(
            'fk-vaccine_uses-id_disease',
            'vaccine_uses'
        );

        // drops index for column `id_disease`
        $this->dropIndex(
            'idx-vaccine_uses-id_disease',
            'vaccine_uses'
        );

        $this->dropTable('vaccine_uses');
    }
}
