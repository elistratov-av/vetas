<?php
namespace app\common\validators;

use yii\validators\Validator;

class InnValidator extends Validator
{
    /**
     * @var bool
     */
    public $is_legal;

    /**
     * @param \app\models\db\PetOwners $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $result = false;
        $error_message = null;

        if (isset($model->is_legal)) {
            $this->is_legal = $model->is_legal;
        }

        $inn = (string) $model->$attribute;
        $inn_length = strlen($inn);

        if (preg_match('/[^0-9]/', $inn)) {
            $error_message = 'ИНН может состоять только из цифр';
        } elseif ($this->is_legal === true && $inn_length != 10 ) { // Юр лица
            $error_message = 'ИНН юр. лица может состоять только из 10 цифр';
        } elseif ($this->is_legal === false && $inn_length != 12) { // Физ. лица
            $error_message = 'ИНН физического лица или предпринимателя может состоять только из 12 цифр';
        } elseif (!in_array($inn_length, [10, 12])){
            $error_message = 'ИНН может состоять только из 10 или 12 цифр';
        } elseif ((int)$inn == 0) {
           $error_message = 'ИНН не может состоять только из нулей';
        } else {
            $check_digit = function($inn, $coefficients) {
                $n = 0;
                foreach ($coefficients as $i => $k) {
                    $n += $k * (int) $inn[$i];
                }
                return $n % 11 % 10;
            };
            switch ($inn_length) {
                case 10:
                    $n10 = $check_digit($inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if ($n10 === (int) $inn[9]) {
                        $result = true;
                    }
                    break;
                case 12:
                    $n11 = $check_digit($inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    $n12 = $check_digit($inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if (($n11 === (int) $inn[10]) && ($n12 === (int) $inn[11])) {
                        $result = true;
                    }
                    break;
            }
            if (!$result) {
                $error_message = 'Неправильное контрольное число ИНН';
            }
        }

        if (!$result) {
            $this->addError($model, $attribute, $error_message, [
                'attribute' => $attribute
            ]);
        }
    }
}
