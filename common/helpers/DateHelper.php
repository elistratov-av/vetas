<?php

namespace app\common\helpers;

use DateTime;
use DateTimeInterface;
use yii\db\Exception;

/**
 * Class DateHelper
 *
 * @package app\common\helpers
 */
class DateHelper
{
    /**
     * @param string $dateFrom
     * @param string $dateAt
     * @param string $formatDateFrom
     * @param string $formatDateAt
     * @return string|null
     */
    public static function ageAtDate($dateFrom, $dateAt, $formatDateFrom = 'Y-m-d', $formatDateAt = 'Y-m-d H:i:s')
    {
        try {
            $dateFromObj = date_create_from_format($formatDateFrom, $dateFrom);
            $dateAtObj = date_create_from_format($formatDateAt, $dateAt);
        } catch (\Throwable $e) {
            return null;
        }

        if ($dateFromObj === false || $dateAtObj === false) {
            return null;
        }

        $interval = $dateFromObj->diff($dateAtObj);
        if ($interval === false) {
            return null;
        }

        $years = $interval->format('%y');
        $months = $interval->format('%m');
        $age = '';
        if ($years > 0) {
            $age .= $years;
            $age .= ' ';
            $age .= self::pluralize($years, 'год', 'года', 'лет');
            $age .= ' ';
        }
        if ($months > 0) {
            $age .= $months;
            $age .= ' ';
            $age .= self::pluralize($months, 'месяц', 'месяца', 'месяцев');
            $age .= ' ';
        }
        if ($years == 0 && $months == 0) {
            $age = $interval->days . ' ' . self::pluralize($interval->days, 'день', 'дня', 'дней');
        }

        return trim($age);
    }

    /**
     * @param int $number
     * @param string $one
     * @param string $few
     * @param string $many
     * @return string
     */
    public static function pluralize($number, $one, $few, $many)
    {
        while ($number >= 100) {
            $number = $number % 100;
        }
        while ($number >= 20) {
            $number = $number % 10;
        }

        if ($number == 0 || ($number >= 5 && $number <= 19)) {
            $str = $many;
        } elseif ($number == 1) {
            $str = $one;
        } elseif ($number >= 2 && $number <= 4) {
            $str = $few;
        } else {
            // fallback
            $str = $many;
        }

        return $str;
    }

    /**
     * Форматирует дату в строковое значение
     *
     * @param int|string|DateTime $date
     * @return string
     */
    public static function dateToStr($date): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        } else {
            if (is_integer($date)) {
                $date = new DateTime(date('Y-m-d', $date));
            }
        }

        if (!($date instanceof DateTimeInterface)) {
            throw new Exception('Неизвесный формат даты');
        }

        return $date->format('j') . ' ' . self::monthToStr($date->format('m')) . ' ' . $date->format('Y');
    }

    /**
     * Возвращает месяц прописью
     *
     * @param string|integer $month
     */
    public static function monthToStr($month)
    {
        $month = (int)$month;

        /* @var DateTime $date */
        $monthList = [
            1  => "января",
            2  => "февраля",
            3  => "марта",
            4  => "апреля",
            5  => "мая",
            6  => "июня",
            7  => "июля",
            8  => "августа",
            9  => "сентября",
            10 => "октября",
            11 => "ноября",
            12 => "декабря",
        ];

        if (!array_key_exists($month, $monthList)) {
            throw new Exception('Значение месяца должно быть числовым в пределах 1-12');
        }

        return $monthList[$month];
    }
}
