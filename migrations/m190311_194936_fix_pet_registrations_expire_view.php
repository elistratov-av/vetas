<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_194936_fix_pet_registrations_expire_view
 */
class m190311_194936_fix_pet_registrations_expire_view extends Migration
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
     JOIN organizations ON organizations.id = pets.id_reg_organization
     LEFT JOIN pet_identification ON pets.id = pet_identification.id_pet;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
