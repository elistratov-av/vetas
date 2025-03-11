<?php

namespace app\modules\adminfstek\helpers;

use yii\helpers\ArrayHelper;

/**
 * Class PasswordHelper
 * @package app\modules\adminfstek\helpers
 */
class PasswordHelper
{
    /**
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = null)
    {
        if (empty($length) || $length < 6) {
            $length = ArrayHelper::getValue(\Yii::$app->params, 'password_min_length', 6);
        }

        $security = \Yii::$app->getSecurity();
        $valid = false;
        $string = null;
        while ($valid !== true) {
            $string = $security->generateRandomString($length);
            $valid = self::validatePassword($string, $length);
        }

        return $string;
    }

    /**
     * @param string $string
     * @param int    $length
     * @return bool
     */
    public static function validatePassword($string, $length = null)
    {
        $pattern = self::pattern($length);

        return (preg_match($pattern, $string) === 1);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function pattern($length = null)
    {
        if (empty($length) || $length < 6) {
            $length = ArrayHelper::getValue(\Yii::$app->params, 'password_min_length', 6);
        }

        $letters = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_letters', false);
        $case = ArrayHelper::getValue(\Yii::$app->params, 'password_both_case', false);
        $digits = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_digits', false);
        $symbols = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_symbols', false);

        if ($letters === false && $digits === false && $symbols === false) {
            // fallback
            $letters = true;
        }

        $pattern = '#^';
        if ($letters === true && ($digits === false && $symbols === false)) {
            if ($case === false) {
                $pattern .= '[a-z]';
            } else {
                $pattern .= '(?=.*[a-z])(?=.*[A-Z])[a-zA-Z]';
            }
        } elseif ($digits === true && ($letters === false && $symbols === false)) {
            $pattern .= '\d';
        } elseif ($symbols === true && ($letters === false && $digits === false)) {
            $pattern .= '[\!\@\$\%\^\&\-\+\?]';
        } else {
            $end = '';
            if ($letters === true) {
                $pattern .= '(?=.*[a-z])';
                $end .= 'a-z';
                if ($case === true) {
                    $pattern .= '(?=.*[A-Z])';
                    $end .= 'A-Z';
                }
            }
            if ($digits === true) {
                $pattern .= '(?=.*\d)';
                $end .= '\d';
            }
            if ($symbols === true) {
                $pattern .= '(?=.*[\!\@\$\%\^\&\-\+\?])';
                $end .= '\!\@\$\%\^\&\-\+\?';
            }
            $pattern .= ('[' . $end .']');
        }

        $pattern .= '{' . $length . ',}$#';
        if ($case === false && $letters === true) {
            $pattern .= 'i';
        }

        return $pattern;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function errorMessage($length = null)
    {
        if (empty($length) || $length < 6) {
            $length = ArrayHelper::getValue(\Yii::$app->params, 'password_min_length', 6);
        }

        $message = 'Пароль должен быть длиной не менее ' . $length . ' символов и состоять из ';

        $arr = [];

        $letters = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_letters', false);
        $case = ArrayHelper::getValue(\Yii::$app->params, 'password_both_case', false);
        $digits = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_digits', false);
        $symbols = ArrayHelper::getValue(\Yii::$app->params, 'password_contains_symbols', false);

        if ($letters && $case && $digits && $symbols === false) {
            $letters = true;
        }

        if ($letters === true) {
            $str = 'букв латинского алфавита';
            if ($case === true) {
                $str .= ' в верхнем и нижнем регистре';
            }
            $arr[] = $str;
        }
        if ($digits === true) {
            $arr[] = 'цифр';
        }
        if ($symbols === true) {
            $arr[] = 'спецсимволов !@$%^&-+?';
        }

        return $message . implode(', ', $arr);
    }
}
