<?php

namespace app\common\validators;

use yii\validators\Validator;

/**
 * Class PGFilterEscapesValidator
 * @package app\common\validators
 */
class PGFilterEscapesValidator extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
     * @return void
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if ($value === null && $this->skipOnEmpty === true) {
            return;
        }

        if (!is_string($value)) {
            $this->addError($model, $attribute, "{$attribute} не является строкой.");
            return;
        }

        $model->$attribute = $this->validateValue($value);
    }

    /**
     * @param string $value the data value to be validated.
     * @param string|null $error
     * @return string|null
     */
    public function validate($value, &$error = null)
    {
        if ($value === null && $this->skipOnEmpty === true) {
            return $value;
        }

        return $this->validateValue($value);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function validateValue($value)
    {
        return str_replace("\\", "", $value);
    }
}
