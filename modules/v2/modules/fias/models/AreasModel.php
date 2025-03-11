<?php


namespace app\modules\v2\modules\fias\models;

use app\models\db\Areas;

class AreasModel
{
    public function list()
    {
        return Areas::find()->all();
    }
}
