<?php

use app\commands\migrate\Migration;

/**
 * Class m210422_120004_add_field_to_pet_vaccination
 */
class m210422_120004_add_field_to_pet_vaccination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.pet_rabies_vaccination', 'id_visit_service_tmc', $this->bigInteger()->comment('id услуги ТМЦ'));
        $this->addColumn('public.pet_other_vaccinations', 'id_visit_service_tmc', $this->bigInteger()->comment('id услуги ТМЦ'));

        $this->addForeignKey(
            'fk-pet_rabies_vaccination-visit_service_tmc',
            'public.pet_rabies_vaccination',
            'id_visit_service_tmc',
            'visit_service_tmc',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-pet_other_vaccinations-visit_service_tmc',
            'public.pet_other_vaccinations',
            'id_visit_service_tmc',
            'visit_service_tmc',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pet_rabies_vaccination-visit_service_tmc', 'public.pet_rabies_vaccination');
        $this->dropForeignKey('fk-pet_other_vaccinations-visit_service_tmc', 'public.pet_other_vaccinations');
        $this->dropColumn('public.pet_rabies_vaccination', 'id_visit_service_tmc');
        $this->dropColumn('public.pet_other_vaccinations', 'id_visit_service_tmc');
    }
}
