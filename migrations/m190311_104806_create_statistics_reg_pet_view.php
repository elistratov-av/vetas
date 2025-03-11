<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_104806_create_statistics_reg_pet_view
 */
class m190311_104806_create_statistics_reg_pet_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW statistic.pet_registrations AS 
 SELECT DISTINCT pets.id,
    pets.name,
    pet_identification.id AS pet_identification_id,
    pets.id_reg_organization AS id_organization,
    pets.id_species
   FROM pets
     JOIN reg_certificates ON reg_certificates.id_pet = pets.id
     JOIN organizations ON organizations.id = pets.id_reg_organization
     LEFT JOIN pet_identification ON pets.id = pet_identification.id_pet;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW statistic.pet_registrations_species AS 
 SELECT DISTINCT pet_registrations.id_organization,
    species.id,
    species.name,
    species.description,
    species.created_by,
    species.updated_by,
    species.created_at,
    species.updated_at,
    species.flag_mos_ru,
    species.tech_name
   FROM statistic.pet_registrations
     JOIN species ON species.id = pet_registrations.id_species;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW statistic.pet_registrations_species");
        $this->execute("DROP VIEW statistic.pet_registrations");
    }
}
