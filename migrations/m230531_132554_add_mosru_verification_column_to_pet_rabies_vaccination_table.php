<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pet_rabies_vaccination}}`.
 */
class m230531_132554_add_mosru_verification_column_to_pet_rabies_vaccination_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_rabies_vaccination', 'mosru_verification', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_rabies_vaccination', 'mosru_verification');
    }
}
