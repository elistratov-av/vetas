<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.03.19
 * Time: 14:43
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\ShiftType;
use app\models\db\VisitsGovServices;
use app\modules\admin\models\Organization;
use app\modules\admin\models\Visits;
use app\modules\adminv\models\export\ClinicsDutyReportExport;
use yii\db\Expression;

/**
 * Отчет о нагрузке на ветеринарные учреждения и службы
 *
 * Class ClinicsDutyReportController
 * @package app\modules\adminv\controllers\statistics
 */
class ClinicsDutyReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $areas;
    /**
     * @var array
     */
    public $districts;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->districts = \Yii::$app->request->get('id_district', []);
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $organizations
     * @param array $areas
     * @param array $districts
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $organizations, $areas, $districts)
    {
        $mosruChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
            ->scalar();
        $phoneChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT])
            ->scalar();
        $lqChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE])
            ->scalar();
        $workdayChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY])
            ->scalar();

        $appointmentVisitsQuery = Visits::find()
            ->select('count(*)')
            ->where([
                'id_organization' => new Expression('organizations.id'),
                'channel' => $workdayChannelId,
            ])
            ->andWhere(['between', 'fact_start_dttm', $from, $to]);

        $mosruVisitsQuery = Visits::find()
            ->select('count(*)')
            ->where([
                'id_organization' => new Expression('organizations.id'),
                'channel' => $mosruChannelId,
            ])
            ->andWhere(['between', 'fact_start_dttm', $from, $to]);

        $phoneVisitsQuery = Visits::find()
            ->select('count(*)')
            ->where([
                'id_organization' => new Expression('organizations.id'),
                'channel' => $phoneChannelId,
            ])
            ->andWhere(['between', 'fact_start_dttm', $from, $to]);

        $liveQueueVisitsQuery = Visits::find()
            ->select('count(*)')
            ->where([
                'id_organization' => new Expression('organizations.id'),
                'channel' => $lqChannelId,
            ])
            ->andWhere(['between', 'fact_start_dttm', $from, $to]);

        $totalVisitsQuery = Visits::find()
            ->select('count(*) as total')
            ->where([
                'id_organization' => new Expression('organizations.id'),
            ])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['between', 'fact_start_dttm', $from, $to]);

        $servicesCounter = VisitsGovServices::find()
            ->select('sum(count)')
            ->joinWith('visit', false)
            ->where([
                'id_visit' => new Expression('visits.id'),
                'visits.id_organization' => new Expression('organizations.id'),
            ])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['between', 'visits_gov_services.created_at', $from, $to]);

        $fromQuery = Organization::find()
            ->select([
                'organizations.id',
                'organizations.short_name',
                'fias_addresses.id_area',
                'areas.name as area_name',
                'fias_addresses.id_district',
                'districts.name as dist_name',
                'mosruVisitsQuery' => $mosruVisitsQuery,
                'phoneVisitsQuery' => $phoneVisitsQuery,
                'liveQueueVisitsQuery' => $liveQueueVisitsQuery,
                'appointmentVisitsQuery' => $appointmentVisitsQuery,
                'totalVisitsQuery' => $totalVisitsQuery,
                'servicesCounter' => $servicesCounter,
            ])
            ->joinWith(['fias_addresses', 'visits'], false)
            ->leftjoin('areas', ['areas.id' => new Expression('fias_addresses.id_area')])
            ->leftjoin('districts', ['districts.id' => new Expression('fias_addresses.id_district')])
            ->groupBy([
                'organizations.id',
                'organizations.short_name',
                'fias_addresses.id_area',
                'areas.name',
                'fias_addresses.id_district',
                'districts.name',
            ])
            ->orderBy([
                'areas.name' => SORT_ASC,
                'districts.name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
            ]);

        $mainQuery = VisitsGovServices::find()
            ->from(['t' => $fromQuery])
            ->andWhere(['>', 't.totalVisitsQuery', 0])
            ->asArray();

        if (!empty($organizations)) {
            $mainQuery->andWhere(['t.id' => $organizations]);
        }
        if (!empty($areas)) {
            $mainQuery->andWhere(['t.id_area' => $areas]);
        }
        if (!empty($districts)) {
            $mainQuery->andWhere(['t.id_district' => $districts]);
        }

        $data = $mainQuery->all();

        return $data;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->districts);

        return $this->render('index', [
            'data' => $data,
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
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->districts);
        // $exportedReport = new ClinicsDutyReportExport();
        // $exportedReport->export($data, "Отчет о нагрузке на ветеринарные учреждения и службы c {$this->from} по {$this->to}.xls", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\ClinicsDutyReportExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }
}
