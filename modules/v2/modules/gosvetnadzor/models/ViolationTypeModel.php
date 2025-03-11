<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\models\db\ViolationType;

class ViolationTypeModel
{

    /**
     * Возвращает весь справочник типов нарушений
     *
     * @return ViolationType[]
     */
    public function getAll()
    {
        return ViolationType::find()
            ->orderBy('name')
            ->all();
    }
}
