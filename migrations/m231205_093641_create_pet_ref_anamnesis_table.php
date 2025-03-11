<?php

use yii\db\Migration;

/**
 * Class m231205_093641_create_pet_ref_anamnesis_table
 */
class m231205_093641_create_pet_ref_anamnesis_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_ref_anamnesis', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('pet_ref_anamnesis');
    }
}
