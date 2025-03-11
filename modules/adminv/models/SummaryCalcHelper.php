<?php


namespace app\modules\adminv\models;

use yii\base\InvalidConfigException;

class SummaryCalcHelper
{
    /**
     * Функция суммирования по столбцам
     * Первым аргументом принимает переменную с предыдущей суммой, вторым - текущую строку
     *
     * ВНИМАНИЕ!!!
     * Стоит быть внимательным при использовании:
     * ф-ция складывает все переменные, которые удовлетворяют проверку
     *      is_numeric($total_calc_var[$col_num]) && is_numeric($row[$col_num])
     *
     * Потому может суммировать например, id_organization или пропустить сложение если в $row[$col_num]
     * придет посреди подсчета "левое" значение
     *
     * @param array $total_calc_var
     * @param array $row
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    static function summaryCalc(&$total_calc_var, $row)
    {
        if (empty($total_calc_var)){
            $total_calc_var = $row;
            return $total_calc_var;
        }

        if (!is_array($total_calc_var) && !is_array($row)){
            throw new InvalidConfigException('Возникла проблема при подсчете итоговых значений');
        }

        foreach ($row as $col_name => $col_value){
            if (!is_numeric($col_value)){
                continue; // Пропускаем
            }

            if (!array_key_exists($col_name, $total_calc_var) && is_numeric($col_value)){
                $total_calc_var[$col_name] = $total_calc_var;
                continue;
            }

            if (is_numeric($col_value) && is_numeric($total_calc_var[$col_name])){
                $total_calc_var[$col_name] += $col_value;
            }
        }
    }
}
