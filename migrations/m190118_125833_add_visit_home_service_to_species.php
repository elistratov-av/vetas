<?php

use app\commands\migrate\Migration;

/**
 * Class m190118_125833_add_visit_home_service_to_species
 */
class m190118_125833_add_visit_home_service_to_species extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('species_services', [
            'id_species' => 9,
            'id_service' => 173
        ]);

        $this->insert('species_services', [
            'id_species' => 25,
            'id_service' => 173
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('species_services', [
            'id_species' => 9,
            'id_service' => 173
        ]);

        $this->delete('species_services', [
            'id_species' => 25,
            'id_service' => 173
        ]);
    }

}
