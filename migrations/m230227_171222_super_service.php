<?php

use app\commands\migrate\Migration;

/**
 * Class m230227_171222_super_service
 */
class m230227_171222_super_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_ectoparasites', 'mosru_organization', $this->string()->defaultValue(null));
        $this->addColumn('pet_rabies_vaccination', 'mosru_organization', $this->string()->defaultValue(null));
        $this->addColumn('pet_other_vaccinations', 'mosru_organization', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_ectoparasites', 'mosru_organization');
        $this->dropColumn('pet_rabies_vaccination', 'mosru_organization');
        $this->dropColumn('pet_rabies_vaccination', 'mosru_organization');
    }
}
