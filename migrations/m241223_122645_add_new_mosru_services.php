<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;

/**
 * Class m241223_122645_add_new_mosru_services
 */
class m241223_122645_add_new_mosru_services extends Migration
{

    const SERVICES_IDS = [1831, 1832];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $services = GovServices::find()->where(['id' => static::SERVICES_IDS])->all() ?? [];
        foreach ($services as $service) {
            $service->name = 'Комплексная услуга груминг';
            $service->type = 'mosru';
            $service->save();
            $this->insert('species_services', [
                'id_species' => $service->id == 1831 ? 9 : 25,
                'id_service' => $service->id
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('species_services', [
            'id_service' => static::SERVICES_IDS,
        ]);
        $services = GovServices::find()->where(['id' => static::SERVICES_IDS])->all() ?? [];
        foreach ($services as $service) {
            $service->type = null;
            $service->name = $service->id == 1831 ? 'Груминг кошек (комплекс)' : 'Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см)';
            $service->save();
        }
        return true;
    }
}
