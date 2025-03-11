<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pet_other_vaccinations}}`.
 */
class m230531_132654_add_mosru_verification_column_to_pet_other_vaccinations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_other_vaccinations', 'mosru_verification', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_other_vaccinations', 'mosru_verification');
    }
}
