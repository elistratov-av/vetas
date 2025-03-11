<?php

namespace app\controllers\wtf;

use app\models\db\DocumentTypes as Model;

class DocumentTypesController extends BaseApiController
{
    public $modelClass = Model::class;
}