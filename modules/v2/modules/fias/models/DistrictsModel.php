<?php

namespace app\modules\v2\modules\fias\models;

use app\models\db\Districts;

class DistrictsModel
{
    public function get(int $id_area){
        return Districts::find()
            ->where(['id_area' => $id_area])
            ->all();
    }
}
