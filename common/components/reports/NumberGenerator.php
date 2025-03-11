<?php

namespace app\common\components\reports;

use app\models\db\report\Number;
use DateTime;

/**
 * Генератор номера отчета
 * Class NumberGenerator
 *
 * @package app\common\components\reports
 * @author Aleksandr Roik
 */
class NumberGenerator
{
    /**
     * Возвращает или генерирует новый номер отчета по его хешу
     *
     * @param int $resportId - id отчета
     * @param string $reportVersion - версия отчета
     * @param string $reportHash - Уникальниный хеш отчета
     * @param DateTime $dateTime - Дата, символизирующая период, за который генерируется отчет.
     * @param int|null $length Минимальноее количество знаков в номере, что будет возвращен. Если менше - в начало добавляются нули
     * @return string
     */
    public static function generateWithPeriodYear(int $resportId, string $reportVersion, string $reportHash, DateTime $dateTime, int $length = 3): string
    {
        $number = 1;

        // Ищем в базе номер для этого отчета
        $one = Number::find()
            ->andWhere([
                'id_report' => $resportId,
                'version'   => $reportVersion,
                'hash'      => $reportHash,
            ])
            ->andWhere(['between', 'created_at', $dateTime->format('Y-01-01 00:00:00'), $dateTime->format('Y-12-31 23:59:59')])
            ->one();

        if ($one) {
            $number = $one->number;
        } else {
            // Ищем в базе последний номер за указанный период
            $max = Number::find()
                ->andWhere([
                    'id_report' => $resportId,
                    'version'   => $reportVersion,
                ])
                ->andWhere(['between', 'created_at', $dateTime->format('Y-01-01 00:00:00'), $dateTime->format('Y-12-31 23:59:59')])
                ->max('number');

            // Если нашли - добавляем "+1"
            if ($max) {
                $number = ($max + 1);
            }

            // Сохраняем новый номер
            (new Number())
                ->setAttributes([
                    'id_report'  => $resportId,
                    'version'    => $reportVersion,
                    'hash'       => $reportHash,
                    'number'     => $number,
                    'created_at' => $dateTime->format('Y-m-d H:i:s'),
                ])
                ->save();
        }

        // Форматируем
        if ($length && $length > strlen($number)) {
            $number = sprintf("%'.0" . $length . "d", $number);
        }

        return $number;
    }

    /**
     * !!!
     * Метод заглушка для совместимости со старым кодом
     * УДАЛИТЬ после замени во всех класах на self::generateWithPeriodYear()
     *
     * @param string $reportHash
     * @param int $length
     */
    public static function gererateByHash(string $reportHash, int $length = 3)
    {
        return '001';
    }
}
