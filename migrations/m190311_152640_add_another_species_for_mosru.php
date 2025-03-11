<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_152640_add_another_species_for_mosru
 */
class m190311_152640_add_another_species_for_mosru extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $species = new \app\models\db\Species();
        $species->name = 'иные животные';
        $species->flag_mos_ru = true;
        $species->save(false);

        $breeds = new \app\models\db\Breeds();
        $breeds->name = 'другая';
        $breeds->species_id = $species->id;
        $breeds->sort_by = 1;
        $breeds->save(false);

        $services = \app\models\db\MosRuServices::find()->all();
        foreach ($services as $service) {
            $this->insert('species_services', [
                'id_species' => $species->id,
                'id_service' => $service->id
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $species = \app\models\db\Species::findOne(['name' => 'иные животные', 'flag_mos_ru' => true]);
        if (!$species) {
            return;
        }

        $this->delete('breeds', ['species_id' => $species->id]);
        $this->delete('species_services', ['id_species' => $species->id]);
        $species->delete();
    }
}
