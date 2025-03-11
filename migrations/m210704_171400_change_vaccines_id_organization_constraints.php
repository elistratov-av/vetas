<?php

use app\commands\migrate\Migration;

/**
 * Class m210704_171400_change_vaccines_id_organization_constraints
 */
class m210704_171400_change_vaccines_id_organization_constraints extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-pet_rabies_vaccination-organization', 'pet_rabies_vaccination');
        $this->dropForeignKey('fk-pet_other_vaccinations-organization', 'pet_other_vaccinations');
        $this->dropForeignKey('fk-pet_ectoparasites-organization', 'pet_ectoparasites');
        $this->dropForeignKey('fk-pet_dehelmintization-organization', 'pet_dehelmintization');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey(
            'fk-pet_rabies_vaccination-organization',
            'pet_rabies_vaccination',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_other_vaccinations-organization',
            'pet_other_vaccinations',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_ectoparasites-organization',
            'pet_ectoparasites',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pet_dehelmintization-organization',
            'pet_dehelmintization',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }
}
