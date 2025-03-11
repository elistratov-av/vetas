<?php

use yii\db\Migration;

/**
 * Handles the creation of table `diseases_tmc_types`.
 * Has foreign keys to the tables:
 *
 * - `diseases`
 * - `tmc_types`
 */
class m180613_111431_create_diseases_tmc_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('diseases_tmc_types', [
            'id' => $this->primaryKey(),
            'id_disease' => $this->integer(),
            'id_tmc_type' => $this->integer(),
        ]);

        // creates index for column `id_disease`
        $this->createIndex(
            'idx-diseases_tmc_types-id_disease',
            'diseases_tmc_types',
            'id_disease'
        );

        // add foreign key for table `diseases`
        $this->addForeignKey(
            'fk-diseases_tmc_types-id_disease',
            'diseases_tmc_types',
            'id_disease',
            'diseases',
            'id',
            'CASCADE'
        );

        // creates index for column `id_tmc_type`
        $this->createIndex(
            'idx-diseases_tmc_types-id_tmc_type',
            'diseases_tmc_types',
            'id_tmc_type'
        );

        // add foreign key for table `tmc_types`
        $this->addForeignKey(
            'fk-diseases_tmc_types-id_tmc_type',
            'diseases_tmc_types',
            'id_tmc_type',
            'tmc_types',
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
            'fk-diseases_tmc_types-id_disease',
            'diseases_tmc_types'
        );

        // drops index for column `id_disease`
        $this->dropIndex(
            'idx-diseases_tmc_types-id_disease',
            'diseases_tmc_types'
        );

        // drops foreign key for table `tmc_types`
        $this->dropForeignKey(
            'fk-diseases_tmc_types-id_tmc_type',
            'diseases_tmc_types'
        );

        // drops index for column `id_tmc_type`
        $this->dropIndex(
            'idx-diseases_tmc_types-id_tmc_type',
            'diseases_tmc_types'
        );

        $this->dropTable('diseases_tmc_types');
    }
}
