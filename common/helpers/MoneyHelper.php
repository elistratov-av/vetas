<?php

namespace app\common\helpers;

/**
 * Работа из денежными значениями
 * Class SumHelper
 *
 * @package app\common\helpers
 * @author Aleksandr Roik
 */
class MoneyHelper
{

    /**
     * Возвращает сумму прописью
     *
     * @param $num
     * @param bool $appendKop Добавлять копейки
     * @return string
     */
    public static function sumToStr($num, $appendKop = true)
    {
        $nul = 'ноль';
        $ten = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $a20 = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens = [2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundred = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];
        $unit = [ // Units
            ['копейка', 'копейки', 'копеек', 1],
            ['рубль', 'рубля', 'рублей', 0],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'милиарда', 'миллиардов', 0],
        ];
        //
        [$rub, $kop] = explode('.', sprintf("%015.2f", floatval($num)));
        $out = [];

        $morph = function ($n, $f1, $f2, $f5) {
            $n = abs(intval($n)) % 100;
            if ($n > 10 && $n < 20) {
                return $f5;
            }
            $n = $n % 10;
            if ($n > 1 && $n < 5) {
                return $f2;
            }
            if ($n == 1) {
                return $f1;
            }

            return $f5;
        };

        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                [$i1, $i2, $i3] = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];
                } # 20-99
                else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];
                } # 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = $morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = $morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        if ($appendKop) {
            $out[] = $kop . ' ' . $morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        }

        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    /**
     * Удаляет правые нули из дробной части действительного числа
     *
     * @param float $sum
     */
    public static function removeDecimalVeros(?float $sum, $decimals = 10): ?float
    {
        if($sum === null){
            return null;
        }

        return (float)rtrim((string)number_format($sum, $decimals), '0');
    }
}
