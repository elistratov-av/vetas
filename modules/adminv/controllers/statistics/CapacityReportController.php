<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.06.19
 * Time: 11:19
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\BalanceEquipments;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitServiceTmc;
use app\modules\adminv\models\export\CapacityReportExport;
use yii\db\Expression;

/**
 * Отчет по загрузке мощностей
 *
 * Class CapacityReportController
 * @package app\modules\adminv\controllers\statistics
 */
class CapacityReportController extends StaticticsController
{
    /**
     * @param string $from
     * @param string $to
     * @param array $organizations
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $organizations)
    {
        $mainQuery = Balance::find()
            ->select([
                'balance.id_tmc',
                'balance.id_organization',
                'organizations.name',
                'balance.type_tmc',
                'sum(balance.count)',
                'organizations.short_name',
            ])
            ->andWhere(['balance.type_tmc' => TmcBase::TYPE_EQUIPMENT])
            ->andWhere(['between', new Expression('coalesce("visits"."fact_start_dttm"::date, lower("visits"."time_range")::date, "visits"."created_at"::date)'), $from, $to])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->joinWith([
                'visitServiceTmc',
                'visitServiceTmc.visit',
                'organization',
            ], false)
            ->groupBy([
                'balance.id_tmc',
                'balance.id_organization',
                'organizations.name',
                'organizations.id',
                'balance.type_tmc',
                'organizations.short_name',
            ])
            ->orderBy([
                'organizations.short_name' => SORT_ASC,
                'organizations.name' => SORT_ASC,
            ])
            ->asArray();

        if (!empty($organizations)) {
            $mainQuery->andWhere(['organizations.id' => $organizations]);
        }

        $mainQuery = $mainQuery->all();

        return $mainQuery;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $mainQuery = $this->buildQuery($this->from, $this->to, $this->organizations);

        return $this->render('index', [
            'data' => $mainQuery,
            'organizations' => $this->organizationsOptionsNoShelters(),
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $mainQuery = $this->buildQuery($this->from, $this->to, $this->organizations);
        $exportedReport = new CapacityReportExport();
        $exportedReport->export($mainQuery, "Отчет о загрузке мощностей c {$this->from} по {$this->to}.xls", $this->from, $this->to);
    }
}
