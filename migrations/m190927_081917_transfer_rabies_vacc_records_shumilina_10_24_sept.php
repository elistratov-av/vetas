<?php

use app\commands\migrate\Migration;

/**
 * Class m190927_081917_transfer_rabies_vacc_records_shumilina_10_24_sept
 */
class m190927_081917_transfer_rabies_vacc_records_shumilina_10_24_sept extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $params = [
            ':rabican' => 'Рабикан Вакцина антирабическая инактивированная сухая культуральная из штамма «Щелково-51»',
            ':org' => 662, //гцэвп
            ':spec' => 406, //шумилина светлана юрьевна
            ':start' => '2019-09-10',
            ':endDate' => '2019-09-24',
        ];

        $sql = <<<SQL
INSERT INTO public.pet_rabies_vaccination (id_pet, id_vaccine, drug_name, producer_name, batch, production_date, expiry_date, date, valid_until, id_organization, id_specialist, created_by, updated_by, created_at, updated_at)
SELECT pov.id_pet, pov.id_vaccine, pov.drug_name, pov.producer_name, pov.batch, pov.production_date, pov.expiry_date, pov.date, pov.valid_until, pov.id_organization, pov.id_specialist, pov.created_by, pov.updated_by, pov.created_at, pov.updated_at
FROM public.pet_other_vaccinations as pov
WHERE pov.drug_name = :rabican
AND pov.id_organization = :org
AND pov.id_specialist = :spec
AND pov.created_at::date BETWEEN :start AND :endDate
ON CONFLICT DO NOTHING
SQL;
        $this->execute($sql, $params);

        $sql = <<<SQL
DELETE FROM public.pet_other_vaccinations as pov
WHERE pov.drug_name = :rabican
AND pov.id_organization = :org
AND pov.id_specialist = :spec
AND pov.created_at::date BETWEEN :start AND :endDate
SQL;
        $this->execute($sql, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m190927_081917_transfer_rabies_vacc_records_shumilina_10_24_sept cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190927_081917_transfer_rabies_vacc_records_shumilina_10_24_sept cannot be reverted.\n";

        return false;
    }
    */
}
