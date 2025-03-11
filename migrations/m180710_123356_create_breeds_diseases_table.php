<?php

use yii\db\Migration;

/**
 * Handles the creation of table `breeds_diseases`.
 * Has foreign keys to the tables:
 *
 * - `diseases`
 * - `breeds`
 */
class m180710_123356_create_breeds_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('breeds_diseases', [
            'id' => $this->primaryKey(),
            'id_disease' => $this->integer(),
            'id_breed' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_disease`
        $this->createIndex(
            'idx-breeds_diseases-id_disease',
            'breeds_diseases',
            'id_disease'
        );

        // add foreign key for table `diseases`
        $this->addForeignKey(
            'fk-breeds_diseases-id_disease',
            'breeds_diseases',
            'id_disease',
            'diseases',
            'id',
            'CASCADE'
        );

        // creates index for column `id_breed`
        $this->createIndex(
            'idx-breeds_diseases-id_breed',
            'breeds_diseases',
            'id_breed'
        );

        // add foreign key for table `breeds`
        $this->addForeignKey(
            'fk-breeds_diseases-id_breed',
            'breeds_diseases',
            'id_breed',
            'breeds',
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
            'fk-breeds_diseases-id_disease',
            'breeds_diseases'
        );

        // drops index for column `id_disease`
        $this->dropIndex(
            'idx-breeds_diseases-id_disease',
            'breeds_diseases'
        );

        // drops foreign key for table `breeds`
        $this->dropForeignKey(
            'fk-breeds_diseases-id_breed',
            'breeds_diseases'
        );

        // drops index for column `id_breed`
        $this->dropIndex(
            'idx-breeds_diseases-id_breed',
            'breeds_diseases'
        );

        $this->dropTable('breeds_diseases');
    }
}
