<?php

use app\commands\migrate\Migration;

/**
 * Class m210818_075932_update_valid_until_for_pet_rabies_vaccination
 */
class m210818_075932_update_valid_until_for_pet_rabies_vaccination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $visitVaccinationStation = \app\models\db\Visits::TYPE_VISIT_VC;
        $visitShelter = \app\models\db\Visits::TYPE_VISIT_VC_SHELTER;
        $visitDetour = \app\models\db\Visits::TYPE_VISIT_VC_DETOUR;

        $this->execute('update pet_rabies_vaccination 
set valid_until = date + interval \'1 year\'
where id IN (SELECT prv.id FROM pet_rabies_vaccination prv
left join visit_service_tmc vst on prv.id_visit_service_tmc = vst.id
left join visits v on vst.id_visit = v.id
where v.type IN (\''.$visitVaccinationStation.'\',\''.$visitShelter.'\',\''.$visitDetour.'\'))
AND valid_until is null
');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
