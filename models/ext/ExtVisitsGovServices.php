<?php

namespace app\models\ext;

use app\models\db\VisitsGovServices;

class ExtVisitsGovServices extends VisitsGovServices
{
    public $name;
    public $cur_price;
    public $cod;

    public static function findPetServisesForVisit($id_visit)
    {
        $pss = self::find()
            ->leftJoin('gov_services', 'public.gov_services.id = public.visits_gov_services.id_service')
            ->select(['public.visits_gov_services.*', 'public.gov_services.name', 'public.gov_services.price as cur_price', 'public.gov_services.cod'])
            ->where(['id_visit' => $id_visit])
            //->createCommand()->getRawSql();
        ->all();

        $data = [];
        foreach ($pss as $ps) {
            $data[$ps->id_pet][$ps->cod] = $ps;
        }

        return $data;
    }
}
