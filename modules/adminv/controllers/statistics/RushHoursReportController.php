<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02.04.19
 * Time: 14:45
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\modules\adminv\models\export\RushHoursReportExport;
use app\modules\admin\models\Visits;
use yii\db\Expression;

/**
 * Отчет о пиковых часах загруженности
 *
 * Class RushHoursReportController
 * @package app\modules\adminv\controllers\statistics
 */
class RushHoursReportController extends StaticticsController
{
    /**
     * @param $from
     * @param $to
     * @param $organizations
     * @return array
     */
    public function buildQuery($from, $to, $organizations)
    {
        $fromObj = \DateTime::createFromFormat('Y-m-d', $from);
        $toObj = \DateTime::createFromFormat('Y-m-d', $to);
        $interval = $fromObj->diff($toObj);
        $countDays = $interval->days;
        if ($interval->days == 0) {
            $countDays = 1;
        }
        $fromQuery = Visits::find()
            ->select([
                'hour' => new Expression('distinct extract(hour from visits.fact_start_dttm)'),
                'amount' => new Expression('count(extract(hour from "visits"."fact_start_dttm")) OVER (PARTITION BY extract(hour from "visits"."fact_start_dttm") ORDER BY extract(hour from "visits"."fact_start_dttm"))'),
                'average' => new Expression('count(extract(hour from "visits"."fact_start_dttm")) OVER (PARTITION BY extract(hour from "visits"."fact_start_dttm") ORDER BY extract(hour from "visits"."fact_start_dttm"))::float / :countDays', ['countDays' => $countDays]),
                'visits.id',
                'visits.id_organization',
            ])
            ->groupBy([
                'visits.id',
                'hour',
            ])
            ->orderBy([
                'hour' => SORT_ASC,
            ])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['between', 'visits.fact_start_dttm', $from, $to]);
        $mainQuery = Visits::find()
            ->from(['t' => $fromQuery])
            ->indexBy('hour')
            ->asArray();

        if (!empty($organizations)) {
            $fromQuery->andWhere(['visits.id_organization' => $organizations]);
        }
        $data = $mainQuery->all();
        foreach ($data as &$datum) {
            $datum['average'] = round((double)$datum['average'], 3);
        }
        $averagePerHour = [];
        $amountPerHour = [];
        for ($i = 0; $i < 24; $i++) {
            $averagePerHour += [$i => $data[$i]['average'] ?? 0];
            $amountPerHour += [$i => $data[$i]['amount'] ?? 0];

        }

        $fullData = [$averagePerHour, $amountPerHour];

        return $fullData;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $averagePerHour = $this->buildQuery($this->from, $this->to, $this->organizations)[0];

        return $this->render('index', [
            'avg' => $averagePerHour,
            'from' => $this->from,
            'to' => $this->to,
            'organizations' => $this->organizationsOptionsNoShelters(),
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations)[1];
        // $exportedReport = new RushHoursReportExport();
        // $exportedReport->export($amountPerHour, "Отчет о пиковых часах загруженности c {$this->from} по {$this->to}.xls", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\RushHoursReportExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }
}
