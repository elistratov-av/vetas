<?php

namespace app\controllers\wtf;

use app\models\db\Pets as Model;

class PetController extends BaseApiController
{
    public $modelClass = Model::class;
}
