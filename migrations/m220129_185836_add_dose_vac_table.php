<?php

use app\commands\migrate\Migration;
use app\models\db\PetEctoparasites;
use app\models\db\PetOtherVaccinations;

/**
 * Class m220129_185836_add_dose_vac_table
 */
class m220129_185836_add_dose_vac_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(PetEctoparasites::tableName(), 'dose', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(PetEctoparasites::tableName(), 'dose');
    }
}
