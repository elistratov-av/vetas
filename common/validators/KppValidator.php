<?php
namespace app\common\validators;

use yii\validators\Validator;

class KppValidator extends Validator
{

    public function validateAttribute($model, $attribute)
    {
        $result = false;
        $kpp = (string) $model->$attribute;
        if (strlen($kpp) !== 9) {
            $error_message = 'КПП может состоять только из 9 знаков (цифр или заглавных букв латинского алфавита от A до Z)';
        } else if (!preg_match('/^[0-9]{4}[0-9A-Z]{2}[0-9]{3}$/', $kpp)) {
            $error_message = 'Неправильный формат КПП';
        } else {
            $result = true;
        }
        
        if (!$result) {
            $this->addError($model, $attribute, $error_message, [
                'attribute' => $attribute
            ]);
        }
    }
}
