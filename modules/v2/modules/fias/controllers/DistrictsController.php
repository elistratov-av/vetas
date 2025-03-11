<?php

namespace app\modules\v2\modules\fias\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\fias\models\DistrictsModel;


class DistrictsController extends BaseController
{
    public function actionGet(int $id_area){
        return [
            'result' => (new DistrictsModel())->get($id_area)
        ];
    }
}
