<?php
namespace app\common\validators;

use yii\validators\Validator;

class FilterUcwordsValidator extends Validator
{   
    public $filter;

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_string($value)) {
            $model->$attribute = mb_convert_case($value, MB_CASE_TITLE);
        }
    }
}
