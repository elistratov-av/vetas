<?php

namespace app\common\validators;

use yii\validators\Validator;

class VisitStartDttmValidator extends Validator
{
    /**
     * @param \yii\base\Model|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {   
        if ($model->$attribute < time()) {
            $this->addError($model, $attribute, 'Некорректное время начала приема');
        }
    }

}
