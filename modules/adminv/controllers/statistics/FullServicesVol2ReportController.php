<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 15:48
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\Species;
use app\models\db\Visits;
use app\modules\adminv\models\export\FullServicesReportExport;
use app\modules\adminv\models\export\FullServicesVol2ReportExport;
use app\modules\soap\models\VisitsGovServices;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Общий отчет по приемам
 *
 * Class FullVisitsReportController
 * @package app\modules\adminv\controllers\statistics
 */
class FullServicesVol2ReportController extends StaticticsController
{
    const EMPTY_SUBTOTALS = [
        'total_services' => 0,
        'total_f' => 0,
        'total_a' => 0,
        'total_n' => 0,
        'total_w' => 0,
        'total_t' => 0,
        'total_c' => 0,
        'total_d' => 0,
    ];

    /**
     * @var array
     */
    public $areas;
    /**
     * @var array
     */
    public $districts;
    /**
     * @var array
     */
    public $visit_types;
    /**
     * @var array
     */
    public $channels;
    public $countVisits;
    public $countNew;
    public $countWork;
    /**
     * @var array
     */
    public $organizations;
    /**
     * @var array
     */
    public $species;
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
        $this->channels = \Yii::$app->request->get('channel', []);
        $this->organizations = \Yii::$app->request->get('id_organization', []);
        $this->species = \Yii::$app->request->get('spec_name', []);
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
     * @param $channels
     * @param $organizations
     * @param $species
     * @param $specialists
     * @param $services
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $areas, $districts, $visit_types, $channels, $organizations, $species, $specialists, $services)
    {
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
                'a.name as area_name',
                'o.short_name',
                '(case when v.channel = 1 then \'Направление\' else st.description end) as channel',
                'gs.name as service_name',
                'sum(case when v.status is not null then vgs0.total else 0 end) as total_services',
                "sum(case when v.status = 'F' then vgs0.total else 0 end) as total_f",
                "sum(case when v.status = 'A' then vgs0.total else 0 end) as total_a",
                "sum(case when v.status = 'N' then vgs0.total else 0 end) as total_n",
                "sum(case when v.status = 'W' then vgs0.total else 0 end) as total_w",
                "sum(case when v.status = 'T' then vgs0.total else 0 end) as total_t",
                "sum(case when v.status = 'C' then vgs0.total else 0 end) as total_c",
                "sum(case when v.status = 'D' then vgs0.total else 0 end) as total_d",
            ])
            ->innerJoin(['vgs0' => $vgs0], 'vgs0.id_visit = v.id')
            ->leftJoin('gov_services gs', 'vgs0.id_service = gs.id')
            ->leftJoin('organizations o', 'o.id = v.id_organization')
            ->leftJoin('fias_addresses fa', 'fa.id = o.id_fias_address')
            ->leftJoin('areas a', 'a.id = fa.id_area')
            ->leftJoin('districts d', 'd.id = fa.id_district')
            ->leftJoin('shift_type st', 'st.id = v.channel')
            ->leftJoin('species s', 's.id = vgs0.id_species')
            ->leftJoin('visits_specialists vs', 'vs.id_visit = v.id')
            ->leftJoin('specialists sp', 'sp.id = vs.id_specialist')
            ->leftJoin('users u', 'u.id = sp.id_user')
            ->andWhere(['between', new Expression('coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::date'), $from, $to])
            ->andWhere(['v.type' => $this->visit_types])
            ->groupBy([
                'date',
                'gs.name',
                'a.name',
                'o.short_name',
                'st.description',
                'v.channel'
            ])
            ->orderBy([
                'date' => SORT_ASC,
                'a.name' => SORT_ASC,
                'o.short_name' => SORT_ASC,
                'st.description' => SORT_ASC,
                'gs.name' => SORT_ASC,
            ])
        ->asArray();

        if (!empty($channels)) {
            $mainQuery->andWhere(['v.channel' => $channels]);
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
            $mainQuery->andWhere(['sp.id_user' => $specialists]);
        }
        if (!empty($services)) {
            $mainQuery->andWhere(['vgs0.id_service' => $services]);
        }

        if(!empty($species)) {
            $conditionMap = [
                'CAT' => "s.tech_name = 'CAT'",
                'DOG' => "s.tech_name = 'DOG'",
                'OTHER' => "(s.tech_name = 'HORSE' or s.tech_name is null)"
            ];

            if(count($species) == 1) {
                $mainQuery->andWhere($conditionMap[$species[0]]);
            } elseif (count($species) > 1) {
                $condition[] = 'or';
                foreach ($species as $item) {
                    $condition[] = $conditionMap[$item];
                }
            }
        }

        return $mainQuery;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->channels, $this->organizations, $this->species, $this->specialists, $this->services);

        $speciesList = [
            'CAT' => 'Кошки',
            'DOG' => 'Собаки',
            'OTHER' => 'Иные животные'
        ];

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
        $subtotals = empty($rows) ? static::EMPTY_SUBTOTALS : $this->calculateSubtotals();

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
            'visit_types' => $this->visitTypesOptions(),
            'species' => $speciesList,
            'services' => $this->servicesOptions(),
            'specialists' => $this->usersOptions()
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport(){
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->channels, $this->organizations, $this->species, $this->specialists, $this->services)->asArray()->all();
         $exportedReport = new FullServicesVol2ReportExport();
         $exportedReport->export($data, "Detalniy otchet po uslugam s {$this->from} po {$this->to}.xlsx", $this->from, $this->to);


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
        $data = $this->buildQuery($this->from, $this->to, $this->areas, $this->districts, $this->visit_types, $this->channels, $this->organizations, $this->species, $this->specialists, $this->services)->asArray()->all();

        $subtotals = static::EMPTY_SUBTOTALS;

        foreach ($data as $datum) {
            $subtotals['total_services'] += $datum['total_services'];
            $subtotals['total_f'] += $datum['total_f'];
            $subtotals['total_a'] += $datum['total_a'];
            $subtotals['total_n'] += $datum['total_n'];
            $subtotals['total_w'] += $datum['total_w'];
            $subtotals['total_t'] += $datum['total_t'];
            $subtotals['total_c'] += $datum['total_c'];
            $subtotals['total_d'] += $datum['total_d'];
        }

        return $subtotals;
    }
}
