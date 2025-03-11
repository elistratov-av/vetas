<?php
namespace app\common\validators;

use yii\validators\Validator;

class OgrnValidator extends Validator
{

    public function validateAttribute($model, $attribute)
    {
        $result = false;
        $ogrn = (string) $model->$attribute;
        if (preg_match('/[^0-9]/', $ogrn)) {
            $error_message = 'ОГРН может состоять только из цифр';
        } elseif (strlen($ogrn) !== 13) {
            $error_message = 'ОГРН может состоять только из 13 цифр';
        } elseif ((int)$ogrn == 0) {
            $error_message = 'ОГРН не может состоять только из нулей';
        } else {
            $n13 = (int) substr(bcsub(substr($ogrn, 0, -1), bcmul(bcdiv(substr($ogrn, 0, -1), '11', 0), '11')), -1);
            if ($n13 === (int) $ogrn[12]) {
                $result = true;
            } else {
                $error_message = 'Неправильное контрольное число ОГРН';
            }
        }
        
        if (!$result) {
            $this->addError($model, $attribute, $error_message, [
                'attribute' => $attribute
            ]);
        }
    }
}
