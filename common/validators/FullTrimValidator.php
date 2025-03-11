<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.09.19
 * Time: 15:34
 */

namespace app\common\validators;

use yii\validators\Validator;

/**
 * Class FullTrimValidator
 * @package app\common\validators
 */
class FullTrimValidator extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
     * @return string|string[]|void|null
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

    public function validateValue($value)
    {
        $value = trim($value);
        $value = htmlentities($value, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        $value = str_replace("&nbsp;", ' ', $value);    //неразрывный пробел
        $value = str_replace("&thinsp;", ' ', $value);  //\u2009 тонкий
        $value = str_replace("&#8239;", ' ', $value);   //узкий пробел
        $value = str_replace("&hairsp;", ' ', $value);  //\u200A волосяной
        $value = str_replace("&#8203;", ' ', $value);   //\u200B без ширины, при необходимости переносит слово
        $value = str_replace("&shy;", ' ', $value);     //\u00AD без ширины, при необходимости переносит слово, добавляя к нему дефис
        $value = str_replace("&NoBreak;", ' ', $value); //\u2060 без ширины, неразрывный
        $value = str_replace("&emsp;", ' ', $value);    //\u2003	равен 1em, то есть размеру кегеля
        $value = str_replace("&numsp;", ' ', $value);   //\u2007 равен ширине цифры, если все цифры одинаковой ширины, неразрывный
        $value = str_replace("&puncsp;", ' ', $value);  //\u2008 равен ширине запятой
        $value = str_replace("&blank;", ' ', $value);   //\u2423 обозначение символа
        $value = html_entity_decode($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }
}
