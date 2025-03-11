<?php

namespace app\modules\v2\modules\fias\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\fias\models\AreasModel;


class AreasController extends BaseController
{
    public function actionList(){
        return [
            'result' => (new AreasModel())->list()
        ];
    }
}
