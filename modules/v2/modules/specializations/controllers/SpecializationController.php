<?php

namespace app\modules\v2\modules\specializations\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\specializations\models\SpecializationModel;

class  SpecializationController extends BaseController
{
    public function actionList()
    {
        $specializations = new SpecializationModel();
        return $specializations->list();
    }
}


