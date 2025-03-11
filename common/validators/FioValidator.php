<?php

namespace app\common\validators;

use yii\validators\Validator;

/**
 * Class FioValidator
 * @package app\common\validators
 *
 * Валидация полей согласно требованиям к элементам типа 'text_fio' - «ввод ФИО»
 */
class FioValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $pattern = '/^[A-ZА-ЯЁ]{1}([a-zа-яё]*|[\'\-\s]{1}[A-ZА-ЯЁ]{1}[a-zа-яё]*)([\s\-]{1}[A-ZА-ЯЁ]{1}[a-zа-яё]*)*$/u';
        if (!preg_match($pattern, $value)) {
            return ['Поле должно содержать только латинские или кириллические буквы верхнего или нижнего регистра, апострофы, дефисы или пробелы, а также начинаться с заглавной буквы. Символы дефис и пробел не могут повторяться более одного раза подряд.', []];
        }

        return null;
    }
}
