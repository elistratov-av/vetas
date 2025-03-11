<?php
namespace app\common\validators;

use yii\validators\Validator;

class OnlyNumbersValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (preg_match('/[^0-9]/', (string)$model->$attribute)) {
            $this->addError($model, $attribute, 'Значение «{attribute}» может содержать только цифры', ['attribute' => $attribute]);
        }
    }
}
