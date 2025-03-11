<?php

use yii\db\Migration;

/**
 * Handles dropping id from table `pet_to_skills`.
 */
class m221130_140210_drop_id_column_from_pet_to_skills_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('pet_to_skills', 'id', 'integer');
        $this->dropColumn('pet_to_skills', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
