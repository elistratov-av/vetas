<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.06.19
 * Time: 15:15
 */

namespace app\commands;


use app\common\components\stat\AmbulanceStat;
use DateInterval;
use DatePeriod;
use DateTime;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class AmbulanceReportController extends Controller
{
    /**
     * Отчет по работе НВП на определенную дату
     * @param $from - нижняя временная граница в формате Y-m-d
     * @return int
     * @throws \Throwable
     */
    public function actionAmbulanceReport($from)
    {
        if (!$from) {
            $from = new DateTime(date('Y-m-d', date('Y') . '-01-01'));
        } else {
            $from = new DateTime(date('Y-m-d', strtotime($from)));
        }
        $to = new DateTime(date('Y-m-d'));
        $to = $to->modify('+1 day');
        $period = new DatePeriod($from, new DateInterval('P1D'), $to);
        $dates = array_map(
            function($item){return $item->format('Y-m-d');},
            iterator_to_array($period)
        );
        $db = \Yii::$app->db;
        foreach ($dates as $date) {
            $transaction = $db->beginTransaction();
                $stat = new AmbulanceStat();
                $stat->date = $date;

                if (!$stat->validate()) {
                    throw new \Exception("Неверный формат даты");
                }
                $stat->make();
                $transaction->commit();
        }
        return ExitCode::OK;
    }
}