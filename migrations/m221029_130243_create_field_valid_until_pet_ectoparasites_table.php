<?php

use yii\db\Migration;

/**
 * Handles the creation of table `field_valid_until_pet_ectoparasites`.
 */
class m221029_130243_create_field_valid_until_pet_ectoparasites_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_ectoparasites', 'valid_until', $this->date());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_ectoparasites', 'valid_until');
    }
}
