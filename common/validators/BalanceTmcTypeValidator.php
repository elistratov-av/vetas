<?php

namespace app\common\validators;

use Yii;
use yii\db\Query;
use yii\validators\Validator;

class BalanceTmcTypeValidator extends Validator
{
    /**
     * @param \app\modules\v1\models\EntityResource $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {

//        var_dump($model->getType());
//        die();

        $tmc_class = [
            'id_vaccine' => ['vaccine', 'вакциной'],
            'id_drug' => ['drug', 'препаратом'],
            'id_equipment' => ['equipment', 'оборудованием'],
        ];
        
        if (!isset($tmc_class[$attribute])) {
            return;
        }
        
        $tmc_table = $tmc_class[$attribute][0] . 's';
        
        $check = (new Query())
            ->from($tmc_table)
           // ->join('JOIN', 'tmc_types', 'tmc_types.id = ' . $tmc_table . '.id_tmc_type')
            ->where([$tmc_table . '.id' => $model->$attribute])
            ->count();
        if (!$check) {
            $this->addError($model, $attribute, 'Указанный ТМЦ не является {tmc_type}.', ['tmc_type' => $tmc_class[$attribute][1]]);
            return;
        }

    }
}
