<?php

namespace app\modules\v2\common\models;

use app\models\db\Specialists;
use yii\helpers\ArrayHelper;

/**
 * Trait TimeSheetTrait
 * @package app\modules\v2\common\models
 */
trait TimeSheetTrait
{
    /**
     * Постфильтрация диапазона смен по дате увольнения специалиста при выборках расписаний специалистов
     * (используется, когда дата увольнения специалиста попадает в диапазон, так как сформировать
     * соответствующий sql-запрос затруднительно с учетом формата данных и требуемой структуры ответа)
     *
     * @param array $timeSheets
     * @param array $specialists
     * @see \app\modules\v2\modules\specialist\models\DatelistModel::list
     * @see \app\modules\v2\modules\timesheet\models\TimeSheetModel::list
     */
    protected function filterExpelledSpecialists(array &$timeSheets, array $specialists)
    {
        $specialists = ArrayHelper::index($specialists, 'id');

        foreach ($timeSheets as $key => $timeSheet) {
            $specialist = ArrayHelper::getValue($specialists, $timeSheet['id_specialist']);
            if ($specialist === null) {
                unset($timeSheets[$key]);
                continue;
            }
            if (empty($specialist['expel_date'])) {
                continue;
            }
            $from = substr($timeSheet['from'], 0, 10);
            $to = substr($timeSheet['to'], 0, 10);
            if ($from == $to) {
                // смена в пределах одних суток
                if ($specialist['expel_date'] == $from) {
                    // дата увольнения - этот день (это еще рабочий день)
                    continue;
                }
                if (Specialists::isExpelledAt($specialist['expel_date'], $from)) {
                    unset($timeSheets[$key]);
                }
            } else {
                // смена, переходящая на следующие сутки
                if ($specialist['expel_date'] == $to) {
                    // дата увольнения - день окончания смены (это еще рабочий день)
                    continue;
                }
                if (Specialists::isExpelledAt($specialist['expel_date'], $from)) {
                    // уже на начало смены специалист уволен
                    unset($timeSheets[$key]);
                    continue;
                }
                if (Specialists::isExpelledAt($specialist['expel_date'], $to)) {
                    // на начало смены специалист не был уволен, но окончание смены надо обрезать
                    // (насколько корректно? видел несколько смен, оканчивающихся на 23:59:00)
                    $timeSheet['to'] = $from . ' 23:59:00';
                    $timeSheets[$key] = $timeSheet;
                }
            }
        }

        $timeSheets = array_values($timeSheets);
    }
}
