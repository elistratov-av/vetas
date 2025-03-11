<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 15:48
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\Visits;
use app\modules\adminv\models\export\FullServicesReportExport;
use app\modules\soap\models\VisitsGovServices;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Общий отчет по приемам
 *
 * Class FullVisitsReportController
 * @package app\modules\adminv\controllers\statistics
 */
class FullServicesReportController extends StaticticsController
{
    public $areas;
    public $districts;
    public $visit_types;
    public $statuses;
    public $countVisits;
    public $countNew;
    public $countWork;
    public $organizations;
    public $specialists;
    public $services;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->districts = \Yii::$app->request->get('id_district', []);
        $this->visit_types = \Yii::$app->request->get('visit_types', Visits::types());
        $this->statuses = \Yii::$app->request->get('status', []);
        $this->organizations = \Yii::$app->request->get('id_organization', []);
        $this->specialists = \Yii::$app->request->get('specialists', []);
        $this->services = \Yii::$app->request->get('services', []);

        $this->countVisits = Visits::find()
            ->count();
        $this->countNew = Visits::find()
            ->andWhere(['status' => 'N'])
            ->count();
        $this->countWork = Visits::find()
            ->andWhere(['status' => 'W'])
            ->count();
    }

    /**
     * @param $from
     * @param $to
     * @param $areas
     * @param $districts
     * @param $visit_types
     * @param $statuses
     * @param $organizations
     * @param $specialists
     * @param $services
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $areas, $districts, $visit_types, $statuses, $organizations, $specialists, $services)
    {
        //Решили считать как в детальном отчете по услугам. Сделал Ctrl+C Ctrl+V
        $vgs0SubQuery = VisitsGovServices::find()
            ->alias('vgs')
            ->select([
                'vgs.id_visit',
                'vgs.id_service',
                'p.id_species',
                'count(p.id) as count'
            ])
            ->leftJoin('visit_pets vp', 'vp.id_visit = vgs.id_visit')
            ->leftJoin('pets p', 'p.id = vp.id_pet')
            ->andWhere(new Expression('vgs.id_pet is null'))
            ->groupBy(['vgs.id_visit', 'vgs.id_service', 'p.id_species'])
            ->union(new Expression('select vgs.id_visit, vgs.id_service, p.id_species,
                 sum(case when vgs.count isnull then 1 else vgs.count end) as count
             from visits_gov_services vgs
                      left join pets p on p.id = vgs.id_pet
             group by vgs.id_visit, vgs.id_service, p.id_species'));

        $vgs0 = (new Query())
            ->select([
                'rf.id_visit',
                'rf.id_service',
                'rf.id_species',
                'sum(rf.count) as total'
            ])
            ->from(['rf' => $vgs0SubQuery])
            ->andWhere(new Expression('rf.id_species notnull'))
            ->groupBy(['rf.id_visit', 'rf.id_service', 'rf.id_species']);

        $mainQuery = Visits::find()
            ->alias('v')
            ->select([
                'row_number() over (order by substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) asc) as id',
                'substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) as date',
                'gs.name as service_name',
                'sum(case when st.type = \'LIVE_QUEUE\' then vgs0.total else 0 end) as total_lq',
                'sum(case when st.type = \'PHONE_APPOINTMENT\' then vgs0.total else 0 end) as total_phone',
                'sum(case when st.type = \'WORKDAY\' then vgs0.total else 0 end) as total_workday',
                'sum(case when st.type = \'MOSRU_APPOINTMENT\' and (not (m.service_number ilike \'%-9000005-%\' or m.service_number ilike \'0002%\') or m.service_number isnull) then vgs0.total else 0 end) as total_mosru',
                'sum(case when st.type = \'MOSRU_APPOINTMENT\' and (m.service_number ilike \'%-9000005-%\' or m.service_number ilike \'0002%\')  then vgs0.total else 0 end) as total_mpgu',
                'sum(case when st.type = \'AMBULANCE\' then vgs0.total else 0 end) as total_ambulance',
                'sum(case when st.type = \'MOSRU_CALL_TO_HOME\' then vgs0.total else 0 end) as total_home_mosru',
                'sum(case when st.type = \'VACCINATION_STATION\' then vgs0.total else 0 end) as total_vacc_station',
                'sum(case when st.type = \'DETOUR\' then vgs0.total else 0 end) as total_detour',
                'sum(case when st.type = \'SHELTER\' then vgs0.total else 0 end) as total_shelter',
            ])
            ->innerJoin(['vgs0' => $vgs0], 'vgs0.id_visit = v.id')
            ->leftJoin('gov_services gs', 'vgs0.id_service = gs.id')
            ->leftJoin('etp.message_v2 m', 'm.visit_id = v.id')
            ->leftJoin('organizations o', 'o.id = v.id_organization')
            ->leftJoin('fias_addresses fa', 'fa.id = o.id_fias_address')
            ->leftJoin('areas a', 'a.id = fa.id_area')
            ->leftJoin('districts d', 'd.id = fa.id_district')
            ->leftJoin('visits_specialists vs', 'vs.id_visit = v.id')
            ->leftJoin('specialists s', 's.id = vs.id_specialist')
            ->leftJoin('users u', 'u.id = s.id_user')
            ->leftJoin('shift_type st', 'st.id = v.channel')
            ->andWhere(['between', new Expression('coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::date'), $from, $to])
            ->andWhere(['v.type' => $this->visit_types])
            ->groupBy([
                'date',
                'gs.name'
            ])
            ->orderBy([
                'date' => SORT_ASC,
                'gs.name' => SORT_ASC,
            ])
            ->asArray();

        if (!empty($statuses)) {
            $mainQuery->andWhere(['v.status' => $statuses]);
        }
        if (!empty($areas)) {
            $mainQuery->andWhere(['fa.id_area' => $areas]);
        }
        if (!empty($districts)) {
            $mainQuery->andWhere(['fa.id_district' => $districts]);
        }
        if (!empty($organizations)) {
            $mainQuery->andWhere(['v.id_organization' => $organizations]);
        }
        if (!empty($specialists)) {
            $mainQuery->andWhere(['s.id_user' => $specialists]);
        }
        if (!empty($services)) {
            $mainQuery->andWhere(['vgs0.id_service' => $services]);
        }

        return $mainQuery;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->statuses, $this->organizations, $this->specialists, $this->services);

        $limit = 100;
        $dataProvider = new ActiveDataProvider([
            'query' => $data,
            'pagination' => [
                'defaultPageSize' => $limit,
                'pageSizeLimit' => false,
            ],
        ]);

        $rows = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();
        $subtotals = empty($rows) ? [] : $this->calculateSubtotals();

        return $this->render('index', [
            'rows' => $rows,
            'pagination' => $pagination,
            'subtotals' => $subtotals,
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
            'countVisits' => $this->countVisits,
            'countNew' => $this->countNew,
            'countWork' => $this->countWork,
            'organizations' => $this->organizationsOptionsNoShelters(),
            'areas' => $this->areasOptions(),
            'districts' => $this->districtsOptions(),
            'services' => $this->servicesOptions(),
            'specialists' => $this->usersOptions(),
            'visit_types' => $this->visitTypesOptions(),
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->statuses, $this->organizations, $this->specialists, $this->services)->asArray()->all();
         $exportedReport = new FullServicesReportExport();
         $exportedReport->export($data, "Obshhiy otchet po uslugam s {$this->from} po {$this->to}.xlsx", $this->from, $this->to);


//        $exporter = new \app\modules\adminv\models\excel\FullServicesReportExport([
//            'data' => $data,
//            'from' => $this->from_date,
//            'to' => $this->to_date,
//        ]);

//        $exporter->export();
    }

    /**
     * @return array
     */
    private function calculateSubtotals()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->statuses, $this->organizations, $this->specialists, $this->services)->asArray()->all();

        $subtotals = [
            'total_lq' => 0,
            'total_phone' => 0,
            'total_workday' => 0,
            'total_mosru' => 0,
            'total_mpgu' => 0,
            'total_ambulance' => 0,
            'total_home_mosru' => 0,
            'total_vacc_station' => 0,
            'total_detour' => 0,
            'total_shelter' => 0,
        ];
        foreach ($data as $datum) {
            $subtotals['total_lq'] += $datum['total_lq'];
            $subtotals['total_phone'] += $datum['total_phone'];
            $subtotals['total_workday'] += $datum['total_workday'];
            $subtotals['total_mosru'] += $datum['total_mosru'];
            $subtotals['total_mpgu'] += $datum['total_mpgu'];
            $subtotals['total_ambulance'] += $datum['total_ambulance'];
            $subtotals['total_home_mosru'] += $datum['total_home_mosru'];
            $subtotals['total_vacc_station'] += $datum['total_vacc_station'];
            $subtotals['total_detour'] += $datum['total_detour'];
            $subtotals['total_shelter'] += $datum['total_shelter'];
        }

        return $subtotals;
    }
}
