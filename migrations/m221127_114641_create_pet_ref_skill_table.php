<?php

use yii\db\Migration;

/**
 * Class m221127_114641_create_pet_ref_skill_table
 */
class m221127_114641_create_pet_ref_skill_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_ref_skill', [
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
        $this->dropTable('pet_ref_skill');
    }
}
