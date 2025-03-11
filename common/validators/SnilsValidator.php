<?php
namespace app\common\validators;

use yii\validators\Validator;

class SnilsValidator extends Validator
{

    public function validateAttribute($model, $attribute)
    {
        $result = true;
        $snils = (string) $model->$attribute;
        if (preg_match('/[^0-9]/', $snils)) {
            $result = false;
            $error_message = 'СНИЛС может состоять только из цифр';
        }elseif ((int)$snils == 0) {
            $result = false;
            $error_message = 'СНИЛС не может состоять только из нулей';
        }
        else {
            // Проверка контрольного числа
            // Проверка контрольного числа Страхового номера проводится только для номеров больше номера 001-001-998
            if ((int) $snils > 1001998) {
                $sum = 0;
                for ($i = 0; $i < 9; $i++) {
                    $sum += (int) $snils[$i] * (9 - $i);
                }
                $check_digit = 0;
                if ($sum < 100) {
                    $check_digit = $sum;
                } elseif ($sum > 101) {
                    $check_digit = $sum % 101;
                    if ($check_digit === 100) {
                        $check_digit = 0;
                    }
                }
                if ($check_digit !== (int) substr($snils, -2)) {
                    $result = false;
                    $error_message = 'Неправильное контрольное число СНИЛС';
                }
            }
        }

        if (!$result) {
            $this->addError($model, $attribute, $error_message, [
                'attribute' => $attribute
            ]);
        }
    }
}
