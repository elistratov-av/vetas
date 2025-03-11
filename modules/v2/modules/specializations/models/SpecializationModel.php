<?php

namespace app\modules\v2\modules\specializations\models;
use app\models\db\Specializations;

class SpecializationModel
{
    public function list()
    {
        return Specializations::find()->all();
    }
}
