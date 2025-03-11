<?php
namespace app\common\validators;

use yii\validators\Validator;

class LessThanNowValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (strtotime('now') < strtotime($model->$attribute)) {
            $this->addError($model, $attribute, 'Значение «{attribute}» не может быть позднее текущей даты', ['attribute' => $attribute]);
        }
    }
}
