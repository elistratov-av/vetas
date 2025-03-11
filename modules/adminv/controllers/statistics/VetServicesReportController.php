<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:33
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
//use app\modules\adminv\integration\bi\Api;
//use app\modules\adminv\integration\bi\messages\VetServicesReportRq;
//use app\modules\adminv\integration\bi\messages\VetServicesReportRs;
//use yii\base\InvalidConfigException;
use app\modules\adminv\integration\bi\Api;
use app\modules\adminv\integration\bi\messages\VetServicesReportRq;
use app\modules\adminv\integration\bi\messages\VetServicesReportRs;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
//use yii\web\BadRequestHttpException;
//use yii\web\RangeNotSatisfiableHttpException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Отчет об оказании ветеринарных услуг
 *
 * Class VetServicesReportController
 * @package app\modules\adminv\controllers\statistics
 */
class VetServicesReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $specialists;
    /**
     * @var array
     */
    public $services;
    /**
     * @var array
     */
    public $types;
    /**
     * @var array
     */
    public $organizations;
    /**
     * @var array
     */
    public $channels;

    /**
     * @var array
     */
    public $visit_types;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->specialists = Yii::$app->request->get('specialists', []);
        $this->types = Yii::$app->request->get('type_id', []);
        $this->services = Yii::$app->request->get('id_service', []);
        $this->organizations = Yii::$app->request->get('id_organization', []);
        $this->channels = Yii::$app->request->get('channel', []);
        $this->visit_types = \Yii::$app->request->get('visit_types', Visits::types());
    }

    /**
     * @param $from
     * @param $to
     * @param $organizations
     * @param int|int[] $types
     * @param $services
     * @param $specialists
     * @param $channels
     * @return yii\db\Query
     */
    private function buildQuery($from, $to, $organizations, $types, $services, $specialists, $channels)
    {
        $rabSubQuery = VisitsGovServices::find()
            ->alias('vgs')
            ->select(['vgs.id', 'count(vt.id) as total_rabies'])
            ->innerJoin('visits v', 'vgs.id_visit = v.id')
            ->innerJoin('visit_pets vtp', 'vtp.id_visit = v.id')
            ->leftJoin('visit_service_tmc_pet vsp', 'vsp.id_pet = vtp.id_pet AND vsp.id_visits_gov_service = vgs.id')
            ->leftJoin('visit_service_tmc vt', 'vsp.id_visit_service_tmc = vt.id and vt.type_tmc = \'vaccine\'')
            ->leftJoin('tmc.tmc_to_diseases ttd', 'ttd.id_tmc = vt.id_tmc')
            ->leftJoin('tmc.tmc tmc', 'tmc.id = vt.id_tmc')
            ->leftJoin('diseases d', 'ttd.id_disease = d.id')
            ->andWhere('d.name ilike \'%бешенство%\' AND tmc.name ilike \'%рабикан%\'')
            ->groupBy(['vgs.id']);

        $vsdSubQuery = VisitsGovServices::find()
            ->alias('vgs')
            ->select([
                'vgs.id',
                'count(distinct CASE WHEN d.name = \'ветеринарная справка\' THEN vgs.id END) as total_f4',
                'count(distinct CASE WHEN d.name = \'ветеринарный сертификат\' THEN vgs.id END) as total_ts',
                'count(distinct CASE WHEN d.name = \'ветеринарное свидетельство\' THEN vgs.id END) as total_f1'
            ])
            ->leftJoin('visit_service_param_values vspv', 'vgs.id = vspv.id_visitservice')
            ->innerJoin('dictionaries d', 'vspv.dict_value = d.id')
            ->where('d.type = \'vsdtypes\'')
            ->groupBy(['vgs.id']);

        $fromQuery = VisitsGovServices::find()
            ->alias('vgs')
            ->select([
                'vgs.id_service',
                'service_types.id as type_id',
                'service_types.name as type_name',
                'gov_services.name',
                'visits.id_organization',
                'organizations.short_name',
                'coalesce(areas.name, \'Округ не указан\') as area_name',
                'vgs.price',
                'sum(case when vgs.price_with_discount != 0 then coalesce(vgs.count, 1) else 0 end) as total_paid',
                'sum(vgs.price_with_discount * coalesce(vgs.count, 1)) as total_amount',
                'sum(case when (vgs.apply_discount = true and vgs.price_with_discount = 0.00 and vgs.price != vgs.price_with_discount) or vgs.price = 0.00 then coalesce(vgs.count, 1) else 0 end) as total_free',
                'sum(coalesce(rab.total_rabies, 0)) as total_rabies',
                'sum(case when visits.is_blind = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_blind',
                'sum(case when visits.is_veteran = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_veteran',
                'sum(case when visits.is_disabled = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_disabled',
                'sum(case when visits.is_orphan = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_orphan',
                'sum(case when visits.is_large_family = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_large_family',
                'sum(case when visits.is_veteran_of_labour = true and d.value = 100 then coalesce(vgs.count, 1) else 0 end) as total_veteran_of_labour',
                'sum(coalesce(vsd.total_f1, 0)) as total_f1',
                'sum(coalesce(vsd.total_f4, 0)) as total_f4',
                'sum(coalesce(vsd.total_ts, 0)) as total_ts'
            ])
            ->joinWith([
                'service',
                'service.serviceType',
                'visit',
                'visit.organization',
                'visit.visitsSpecialists',
                'visit.visitsSpecialists.idSpecialist.user',
                'visit.organization.fias_addresses.area'
            ], false)
            ->leftJoin(['rab' => $rabSubQuery], 'rab.id = vgs.id')
            ->leftJoin(['vsd' => $vsdSubQuery], 'vsd.id = vgs.id')
            ->leftJoin('visit_price vp', 'vp.id_visit = visits.id')
            ->leftJoin('discount d', 'd.id = vp.id_discount')
            ->groupBy([
                'gov_services.name',
                'vgs.id_service',
                'visits.id_organization',
                'vgs.price',
                'service_types.id',
                'organizations.short_name',
                'areas.name',
                'service_types.name',
                'rab.total_rabies',
                'vsd.total_f1',
                'vsd.total_f4',
                'vsd.total_ts'
            ])
            ->orderBy([
                'areas.name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
                'service_types.name' => SORT_ASC])
            ->andWhere(['between', new Expression('coalesce("visits"."fact_start_dttm"::date, lower("visits"."time_range")::date, "visits"."created_at"::date)'), $from, $to])
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            ->andWhere(['visits.type' => $this->visit_types])
        ;

        $query = (new Query)
            ->select([
                't.id_service',
                't.type_id',
                't.type_name',
                't.name',
                't.id_organization',
                'sum(t.total_paid) as total_paid',
                'sum(t.total_free) as total_free',
                'sum(t.total_blind) as total_blind',
                'sum(t.total_veteran) as total_veteran',
                'sum(t.total_disabled) as total_disabled',
                'sum(t.total_orphan) as total_orphan',
                'sum(t.total_large_family) as total_large_family',
                'sum(t.total_veteran_of_labour) as total_veteran_of_labour',
                'sum(t.total_rabies) as total_rabies',
                'sum(t.total_f1) as total_f1',
                'sum(t.total_f4) as total_f4',
                'sum(t.total_ts) as total_ts',
                't.price',
                new Expression('sum(t.total_amount) as total_amount'),
                't.short_name',
                't.area_name'
            ])
            ->from(['t' => $fromQuery])
            ->groupBy([
                't.name',
                't.id_organization',
                't.id_service',
                't.price',
                't.type_id',
                't.type_name',
                't.short_name',
                't.area_name'
            ])
            ->orderBy([
                't.area_name' => SORT_ASC,
                't.short_name' => SORT_ASC,
                't.name' => SORT_ASC]);

        if (!empty($specialists)) {
            $fromQuery->andWhere(['users.id' => $specialists]);
        }
        if (!empty($organizations)) {
            $fromQuery->andWhere(['visits.id_organization' => $organizations]);
        }
        if (!empty($services)) {
            $fromQuery->andWhere(['vgs.id_service' => $services]);
        }
        if (!empty($types)) {
            $fromQuery->andWhere(['service_types.id' => $types]);
        }
        if (!empty($channels)) {
            $fromQuery->andWhere(['visits.channel' => $channels]);
        }

        return $query;
    }


    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->types, $this->services, $this->specialists, $this->channels);

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
            'specialists' => $this->usersOptions(),
            'services' => $this->servicesOptions(),
            'serviceTypes' => $this->serviceTypesOptions(),
            'visit_types' => $this->visitTypesOptions(),
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

//    /**
//     * @throws \PhpOffice\PhpSpreadsheet\Exception
//     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
//     * @throws \yii\web\RangeNotSatisfiableHttpException
//     */
//    public function actionExport()                                        // возможно этот метод когда-нибудь понадобится. а может и нет...
//    {
//        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->types, $this->services, $this->specialists, $this->channels);
        // $exportedReport = new ServicesReportExport();
        // $exportedReport->export($mainQuery, "Отчет по контролю спроса c {$this->from} по {$this->to}.xls", $this->from, $this->to);

//        $exporter = new \app\modules\adminv\models\excel\ServicesReportExport([
//            'query' => $query,
//            'from' => $this->from,
//            'to' => $this->to,
//        ]);
//
//        $exporter->export();
//    }

    /**
     * @return array
     */
    private function calculateSubtotals()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->types, $this->services, $this->specialists, $this->channels);
        $query->select([
            't.id_service',
            't.type_id',
            't.type_name',
            't.name',
            't.id_organization',
            't.total_paid',
            't.total_free',
            't.total_blind',
            't.total_veteran',
            't.total_disabled',
            't.total_orphan',
            't.total_large_family',
            't.total_veteran_of_labour',
            't.total_rabies',
            't.total_f1',
            't.total_f4',
            't.total_ts',
            't.price',
            new Expression('sum(t.total_amount) as total_amount'),
            't.short_name',
        ]);
        $query->orderBy([]);
        $query->groupBy([
            't.name',
            't.id_organization',
            't.total_paid',
            't.total_free',
            't.total_blind',
            't.total_veteran',
            't.total_disabled',
            't.total_orphan',
            't.total_large_family',
            't.total_veteran_of_labour',
            't.total_rabies',
            't.total_f1',
            't.total_f4',
            't.total_ts',
            't.id_service',
            't.price',
            't.type_id',
            't.type_name',
            't.short_name',
        ]);

        $result = $query->all();
        $subtotals = [];

        $subtotals['total'] = [
            'total_paid' => 0,
            'total_amount' => 0,
            'total_free' => 0,
            'total_blind' => 0,
            'total_veteran' => 0,
            'total_disabled' => 0,
            'total_orphan' => 0,
            'total_large_family' => 0,
            'total_veteran_of_labour' => 0,
            'total_rabies' => 0,
            'total_f1' => 0,
            'total_f4' => 0,
            'total_ts' => 0,
        ];

        foreach ($result as $i => $row) {
            $subtotals['total']['total_paid'] += $row['total_paid'];
            $subtotals['total']['total_amount'] += $row['total_amount'];
            $subtotals['total']['total_free'] += $row['total_free'];
            $subtotals['total']['total_blind'] += $row['total_blind'];
            $subtotals['total']['total_veteran'] += $row['total_veteran'];
            $subtotals['total']['total_disabled'] += $row['total_disabled'];
            $subtotals['total']['total_orphan'] += $row['total_orphan'];
            $subtotals['total']['total_large_family'] += $row['total_large_family'];
            $subtotals['total']['total_veteran_of_labour'] += $row['total_veteran_of_labour'];
            $subtotals['total']['total_rabies'] += $row['total_rabies'];
            $subtotals['total']['total_f1'] += $row['total_f1'];
            $subtotals['total']['total_f4'] += $row['total_f4'];
            $subtotals['total']['total_ts'] += $row['total_ts'];
        }
        return $subtotals;
    }

    /**
     * @return Response
     */
    public function actionBiExport()
    {
        /** @var Api $api */
        $api = $this->module->get('biApi');
        /** @var VetServicesReportRs $rs */
        $rs = $api->sent(new VetServicesReportRq([
            'from' => $this->from,
            'to' => $this->to,
            'channels' => $this->channels,
            'types' => $this->types,
            'services' => $this->services,
            'organizations' => $this->organizations,
            'specialists' => $this->specialists,
        ]));

        if (!$rs->isOk) {
            throw new BadRequestHttpException('Во время выполнения команды произошла ошибка. Пожалуйста, повторите попытку позднее.');
        }

        /** @var Response $response */
        $response = $this->module->get('response');

        $response->sendContentAsFile($rs->data, $rs->fileName, [
            'mimeType' => $rs->mimeType
        ]);
    }
}
