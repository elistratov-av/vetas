<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.03.19
 * Time: 14:12
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\VisitPrice;
use app\modules\adminv\models\export\EmployeesReportExport;
use app\modules\admin\models\VisitsSpecialist;
use yii\db\Expression;

/**
 * Отчет по работе сотрудников
 *
 * Class EmployeesReportController
 * @package app\modules\adminv\controllers\statistics
 */
class EmployeesReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $areas;
    /**
     * @var
     */
    public $specialists;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->specialists = \Yii::$app->request->get('id', []);
    }

    /**
     * @param string $from
     * @param string $to
     * @param array  $organizations
     * @param array  $areas
     * @param array  $specialists
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $organizations, $areas, $specialists)
    {
        $totalAmountSubQuery = VisitPrice::find()
            ->select([
                new Expression('distinct visits_specialists.id_visit'),
                'visits_specialists.id_specialist',
                'sum(visit_price.price_with_discount) as total',
                'count(visit_price.id_visit)',
            ])
            ->joinWith('visit.specialists', false)
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['between', 'visits.fact_start_dttm', $from, $to])
            ->groupBy(['visits_specialists.id_visit', 'visits_specialists.id_specialist']);

        $totalAmountQuery = VisitPrice::find()
            ->select([
                'sum(total)',
            ])
            ->from(['tt' => $totalAmountSubQuery])
            ->andWhere(['tt.id_specialist' => new Expression('specialists.id')])
            ->groupBy(['tt.id_specialist']);

        $mainQuery = VisitsSpecialist::find()
            ->select([
                'users.id',
                'specialists.id',
                'users.fullname',
                'specialists.id_organization',
                'fias_addresses.id_area',
                new Expression('count(distinct visits_specialists.id_visit) as total_visits'),
                new Expression('sum(coalesce(visits_gov_services.count, 1)) as total_services'),
                'total_amount' => $totalAmountQuery,
                new Expression('sum(case when visits_gov_services.apply_discount = true and discount.value = 100 then coalesce(visits_gov_services.count, 1) else 0 end) as total_free_services'),
                'areas.name',
                'organizations.short_name',
            ])
            ->joinWith([
                'specialist.user',
                'visits',
                'visits.visitsGovServices',
                'visits.visitPrice',
                'visits.organization.fias_addresses',
                'visits.organization.fias_addresses.area',
                'visits.visitPrice.discount',
            ], false)
            ->groupBy([
                'users.id',
                'specialists.id_organization',
                'specialists.id',
                'fias_addresses.id_area',
                'areas.name',
                'organizations.short_name',
            ])
            ->orderBy([
                'areas.name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
                'users.fullname' => SORT_ASC])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['between', 'visits.fact_start_dttm', $from, $to])
            ->asArray();

        if (!empty($organizations)) {
            $mainQuery->andWhere(['visits.id_organization' => $organizations]);
        }
        if (!empty($areas)) {
            $mainQuery->andWhere(['fias_addresses.id_area' => $areas]);
        }
        if (!empty($specialists)) {
            $mainQuery->andWhere(['users.id' => $specialists]);
        }

        $data = $mainQuery->all();

        return $data;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->specialists);

        return $this->render('index', [
            'data' => $data,
            'organizations' => $this->organizationsOptionsNoShelters(),
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->specialists);
        // $exportedReport = new EmployeesReportExport();
        // $exportedReport->export($data, "Отчет по работе сотрудников c {$this->from} по {$this->to}.xls", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\EmployeesReportExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }
}
