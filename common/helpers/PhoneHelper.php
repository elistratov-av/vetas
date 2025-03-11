<?php

namespace app\common\helpers;

class PhoneHelper
{
    /**
     * Преобразование номера телефона передаваемого через mos.ru
     * Номер приходит в формате (990) 000-00-00
     * Метод извлекает только цифры из телефонного номера и возвращает в формате +79998880011
     *
     * @param string $phone
     * @return string
     */
    public static function extractMosRuPhoneNumber($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $len = mb_strlen($phone);

        if ($len != 10 && $len != 11) {
            return null;
        }

        if ($len == 10) {
            // на случай если начнут передавать 7, чтобы у нас ничего не поломалось
            $phone = '7' . $phone;
        }

        return '+' . $phone;
    }
}
