<?php

use app\commands\migrate\Migration;

/**
 * Handles the creation of table `diseases_dosages`.
 */
class m190808_044843_create_diseases_dosages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('diseases_dosages', [
            'id' => $this->primaryKey(),
            'id_dosage' => $this->integer()->notNull(),
            'id_disease' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        $this->addForeignKey('fk-diseases_dosages-id_dosage',
            'diseases_dosages',
            'id_dosage',
            'dosages',
            'id',
        'CASCADE'
        );

        $this->addForeignKey('fk-diseases_dosages-id_disease',
            'diseases_dosages',
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
        $this->dropTable('diseases_dosages');
    }
}
