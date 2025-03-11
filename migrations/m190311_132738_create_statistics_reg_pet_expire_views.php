<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_132738_create_statistics_reg_pet_expire_views
 */
class m190311_132738_create_statistics_reg_pet_expire_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW statistic.pet_registrations_expire AS 
 SELECT DISTINCT pets.id,
    pets.name,
    pet_identification.id AS pet_identification_id,
    pets.id_reg_organization AS id_organization,
    pets.id_species
   FROM pets
     JOIN reg_expire_reasons ON reg_expire_reasons.id = pets.id_reg_expire_reason
     JOIN reg_certificates ON reg_certificates.id_pet = pets.id
     JOIN organizations ON organizations.id = pets.id_reg_organization
     LEFT JOIN pet_identification ON pets.id = pet_identification.id_pet;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE VIEW statistic.pet_registrations_species_expire AS 
 SELECT DISTINCT pet_registrations_expire.id_organization,
    species.id,
    species.name,
    species.description,
    species.created_by,
    species.updated_by,
    species.created_at,
    species.updated_at,
    species.flag_mos_ru,
    species.tech_name
   FROM statistic.pet_registrations_expire
     JOIN species ON species.id = pet_registrations_expire.id_species;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW statistic.pet_registrations_species_expire");
        $this->execute("DROP VIEW statistic.pet_registrations_expire");
    }
}
