<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:33
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\VisitsGovServices;
use app\modules\adminv\models\export\ServicesReportExport;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Отчет по контролю спроса
 *
 * Class ServicesReportController
 * @package app\modules\adminv\controllers\statistics
 */
class ServicesReportController extends StaticticsController
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
     * @var array
     */
    public $types;
    /**
     * @var array
     */
    public $channels;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->districts = \Yii::$app->request->get('id_district', []);
        $this->types = \Yii::$app->request->get('type_id', []);
        $this->channels = \Yii::$app->request->get('channel', []);
    }

    /**
     * @param string    $from
     * @param string    $to
     * @param int|int[] $organizations
     * @param int|int[] $areas
     * @param int|int[] $districts
     * @param int|int[] $types
     * @param int|int[] $channels
     * @return \yii\db\Query
     */
    private function buildQuery($from, $to, $organizations, $areas, $districts, $types, $channels)
    {
        $fromQuery = VisitsGovServices::find()
            ->select([
                'visits_gov_services.id_service',
                'service_types.id as type_id',
                'service_types.name as type_name',
                'gov_services.name',
                'service_types.id',
                'service_types.name as type_name',
                'id_organization',
                'organizations.short_name',
                'fias_addresses.id_area',
                'fias_addresses.id_district',
                new Expression('"areas"."name" as area'),
                new Expression('"districts"."name" as dist'),
                'visits.channel',
                'visits.source',
                new Expression('sum(coalesce(count, 1)) as sum'),
                'visits_gov_services.price',
                new Expression('sum(coalesce(count, 1)) * visits_gov_services.price as total_amount'),
            ])
            ->joinWith([
                'service',
                'service.serviceType',
                'visit',
                'visit.owner.fias_addresses',
                'visit.organization',
            ], false)
            ->leftjoin('areas', ['areas.id' => new Expression('fias_addresses.id_area')])
            ->leftjoin('districts', ['districts.id' => new Expression('fias_addresses.id_district')])
            ->groupBy([
                'gov_services.name',
                'visits_gov_services.id_service',
                'visits.id_organization',
                'fias_addresses.id_area',
                'fias_addresses.id_district',
                'areas.name',
                'districts.name',
                'visits.channel',
                'visits.source',
                'gov_services.id',
                'visits_gov_services.price',
                'fias_addresses.id_district',
                'service_types.id',
                'service_types.name',
                'organizations.short_name',
            ])
            ->orderBy([
                'areas.name' => SORT_ASC,
                'districts.name' => SORT_ASC,
                'visits.id_organization' => SORT_ASC,
                'service_types.name' => SORT_ASC])
            ->andWhere(['between', new Expression('coalesce("visits"."fact_start_dttm"::date, lower("visits"."time_range")::date, "visits"."fact_start_dttm"::date)'), $from, $to])
            ->andWhere(['visits.status' => VisitStatus::FINISHED]);

        if (!empty($organizations)) {
            $fromQuery->andWhere(['id_organization' => $organizations]);
        }
        if (!empty($areas)) {
            $fromQuery->andWhere(['fias_addresses.id_area' => $areas]);
        }
        if (!empty($districts)) {
            $fromQuery->andWhere(['fias_addresses.id_district' => $districts]);
        }
        if (!empty($types)) {
            $fromQuery->andWhere(['service_types.id' => $types]);
        }
        if (!empty($channels)) {
            $fromQuery->andWhere(['visits.channel' => $channels]);
        }

        $query = (new Query)
            ->select([
                't.id_service',
                't.type_id',
                't.type_name',
                't.name',
                't.id_organization',
                't.id_area',
                't.area',
                't.id_district',
                't.dist',
                new Expression('sum(t.sum) as sum'),
                't.price',
                new Expression('sum(t.total_amount) as total_amount'),
                't.short_name',
            ])
            ->from(['t' => $fromQuery])
            ->groupBy([
                't.name',
                't.id_organization',
                't.id_area',
                't.area',
                't.id_district',
                't.dist',
                't.id_service',
                't.price',
                't.type_id',
                't.type_name',
                't.short_name',
            ])
            ->orderBy([
                't.area' => SORT_ASC,
                't.dist' => SORT_ASC,
                't.short_name' => SORT_ASC,
                't.type_name' => SORT_ASC]);

        return $query;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->districts, $this->types, $this->channels);

        $limit = 100;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'organizations' => $this->organizationsOptionsNoShelters(),
            'areas' => $this->areasOptions(),
            'districts' => $this->districtsOptions(),
            'serviceTypes' => $this->serviceTypesOptions(),
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
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->districts, $this->types, $this->channels);
        // $exportedReport = new ServicesReportExport();
        // $exportedReport->export($mainQuery, "Отчет по контролю спроса c {$this->from} по {$this->to}.xls", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\ServicesReportExport([
            'query' => $query,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }

    /**
     * @return array
     */
    private function calculateSubtotals()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas, $this->districts, $this->types, $this->channels);
        $query->select([
            't.type_id',
            't.id_organization',
            't.id_area',
            't.id_district',
            new Expression('sum(t.sum) as sum'),
            new Expression('sum(t.total_amount) as total_amount'),
            new Expression('count(distinct t.id_service) as service_count'),
        ]);
        $query->orderBy([]);
        $query->groupBy([
            't.type_id',
            't.id_organization',
            't.id_area',
            't.id_district',
        ]);

        $result = $query->all();

        $subtotals = [];

        $perArea = array_fill_keys(array_filter(array_unique(ArrayHelper::getColumn($result, 'id_area'))), []);
        $perArea[0] = [];
        $subtotals['total'] = [
            'sum' => 0,
            'total_amount' => 0,
            'service_count' => 0,
        ];

        foreach ($result as $i => $row) {
            $id_area = (int)$row['id_area'];
            $id_district = (int)$row['id_district'];
            $id_organization = (int)$row['id_organization'];
            if (!array_key_exists('total', $perArea[$id_area])) {
                $perArea[$id_area]['total'] = [
                    'sum' => 0,
                    'total_amount' => 0,
                    'service_count' => 0,
                ];
            }
            if (!array_key_exists($id_district, $perArea[$id_area])) {
                $perArea[$id_area][$id_district] = [
                    'total' => [
                        'sum' => 0,
                        'total_amount' => 0,
                        'service_count' => 0,
                    ],
                ];
            }
            if (!array_key_exists($id_organization, $perArea[$id_area][$id_district])) {
                $perArea[$id_area][$id_district][$id_organization] = [
                    'total' => [
                        'sum' => 0,
                        'total_amount' => 0,
                        'service_count' => 0,
                    ],
                ];
            }
            $perArea[$id_area][$id_district][$id_organization][$row['type_id']] = $row;
            $perArea[$id_area][$id_district][$id_organization]['total']['sum'] += $row['sum'];
            $perArea[$id_area][$id_district][$id_organization]['total']['total_amount'] += $row['total_amount'];
            $perArea[$id_area][$id_district][$id_organization]['total']['service_count'] += $row['service_count'];

            $perArea[$id_area][$id_district]['total']['sum'] += $row['sum'];
            $perArea[$id_area][$id_district]['total']['total_amount'] += $row['total_amount'];
            $perArea[$id_area][$id_district]['total']['service_count'] += $row['service_count'];

            $perArea[$id_area]['total']['sum'] += $row['sum'];
            $perArea[$id_area]['total']['total_amount'] += $row['total_amount'];
            $perArea[$id_area]['total']['service_count'] += $row['service_count'];

            $subtotals['total']['sum'] += $row['sum'];
            $subtotals['total']['total_amount'] += $row['total_amount'];
            $subtotals['total']['service_count'] += $row['service_count'];
        }

        $subtotals['perArea'] = $perArea;

        return $subtotals;
    }
}
