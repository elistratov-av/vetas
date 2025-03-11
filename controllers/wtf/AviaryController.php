<?php

namespace app\controllers\wtf;

use app\models\db\Aviary as Model;

class AviaryController extends BaseApiController
{
    public $modelClass = Model::class;
}
