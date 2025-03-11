<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.03.19
 * Time: 14:33
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\found_pet\Ad;
use app\models\db\VisitsGovServices;
use app\modules\adminv\integration\bi\Api;
use app\modules\adminv\integration\bi\messages\SearchPetsReportRq;
use app\modules\adminv\integration\bi\messages\VetServicesReportRs;
use app\modules\adminv\models\export\ServicesReportExport;
use app\modules\foundPet\models\SearchModel;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;

/**
 * Отчет по поиску животных
 *
 * Class SearchPetsReportController
 * @package app\modules\adminv\controllers\statistics
 */
class SearchPetsReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $adType;
    /**
     * @var array
     */
    public $species;
    /**
     * @var string
     */
    public $fromDate;
    /**
     * @var string
     */
    public $toDate;
    /**
     * @var string
     */
    public $createdFrom;
    /**
     * @var string
     */
    public $createdTo;
    /**
     * @var array
     */
    public $adStatus;
    /**
     * @var array
     */
    public $moderator;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->adType = \Yii::$app->request->get('type', []);
        $this->species = \Yii::$app->request->get('species', []);
        $this->adStatus = \Yii::$app->request->get('status', []);
        $this->fromDate = \Yii::$app->request->get('fromDate', date('Y') . '-' . date('M') . '-01');
        $this->toDate = \Yii::$app->request->get('toDate', date('Y-m-d'));
        $this->createdFrom = \Yii::$app->request->get('createdFrom', date('Y') . '-' . date('M') . '-01');
        $this->createdTo = \Yii::$app->request->get('createdTo', date('Y-m-d'));
        $this->moderator = \Yii::$app->request->get('moderator', []);
        if (empty($this->createdFrom)) {
            $date = new \DateTime(date("Y-m") . '-01');
            $this->createdFrom = $date->format('Y-m-d');
        }
        if (empty($this->createdTo)) {
            $this->createdTo = (new \DateTime())->format('Y-m-d');
        }
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param $adType
     * @param $species
     * @param $adStatus
     * @param $createdFrom
     * @param $createdTo
     * @return array
     */
    private function buildQuery($fromDate, $toDate, $adType, $species, $adStatus, $createdFrom, $createdTo)
    {
        $fromQuery = Ad::find()
            ->alias('a')
            ->select([
                'a.id',
                'a.photo',
                '(case when a.type = \'L\' then \'Потеряно\' else \'Найдено\' end) as ad_type',
                'a.animal_name',
                's.name as species_name',
                'b.name as breed_name',
                'a.age as years_age',
                '(case when a.sex = \'1\' then \'Женский\' else \'Мужской\' end) as gender',
                'c.name as colour',
                'a.chip',
                'a.date_event',
                'a.time_event',
                'concat(aa.last_name, \' \', aa.first_name, \' \', aa.middle_name) as fullname',
                'concat(\'+7\', aa.phone) as phone',
                'aa.email',
                'aa2.pobox as lost_found_address',
                'a.notice',
                'u.fullname as moderator',
                'a.stamp',
                'aa.sso_id',
                'a.service_number',
                'a.verify_status',
                '(case when a.closed_by = \'AUTO_CENSOR\' then \'Автоматически отклонённые\'
                     when a.is_active = false and a.closed_by != \'AUTO_CENSOR\' then \'Архивные\'
                     when a.is_active = true and a.verify_status then \'Активные\'
                     when a.is_active = true and a.verify_status is null then \'На модерации\' end) as ad_status',
                '(case when a.is_active = true and a.verify_status is null and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then 2
                     when a.verify_status = true and a.is_active = true and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then 3
                     when a.is_active = false and a.verify_status = false and closed_by = \'MODERATOR\' then 4
                     when a.is_active = false then 5
                     when closed_by = \'AUTO_CENSOR\' then 6 end) as status',
                'a.verify_at',
                'a.created_at',
                'a.updated_at',
                'a.closed_at',
                'a.closed_reason'
            ])
            ->leftJoin('species s', 's.id = a.id_species')
            ->leftJoin('breeds b', 'b.id = a.id_breed')
            ->leftJoin('colors c', 'c.id = a.id_color')
            ->leftJoin('found_pet.ad_authors aa', 'aa.id = a.id_author')
            ->leftJoin('found_pet.ad_addresses aa2', 'aa2.id = a.id_address')
            ->leftJoin('specialists s2', 's2.id = a.id_specialist')
            ->leftJoin('users u', 'u.id = s2.id_user')
            ->orderBy([
                'a.id' => SORT_DESC,
                'a.date_event' => SORT_DESC,
                'a.time_event' => SORT_DESC])
            ->andWhere(['between', 'a.created_at', $createdFrom, $createdTo]);

        $statFromQuery = Ad::find()
            ->alias('a')
            ->select([
                'count(*) as total_ads',
                'count(distinct case when a.is_active = true and a.verify_status is null and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then a.id else null end) as total_moderating',
                'count(distinct case when a.verify_status = true and a.is_active = true and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then a.id else null end) as total_approved',
                'count(distinct case when a.is_active = false and a.verify_status = false and closed_by = \'MODERATOR\' then a.id else null end) as total_rej_mod',
                'count(distinct case when a.is_active = false then a.id else null end) as total_archived',
                'count(distinct case when closed_by = \'AUTO_CENSOR\' then a.id else null end) as total_autocensored',
                '(case when a.is_active = true and a.verify_status is null and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then 2
                     when a.verify_status = true and a.is_active = true and (closed_by != \'AUTO_CENSOR\' or closed_by is null) then 3
                     when a.is_active = false and a.verify_status = false and closed_by = \'MODERATOR\' then 4
                     when a.is_active = false then 5
                     when closed_by = \'AUTO_CENSOR\' then 6 end) as status',
            ])
            ->groupBy([
                'a.is_active',
                'a.verify_status',
                'a.closed_by'
            ])
            ->andWhere(['between', 'a.created_at', $createdFrom, $createdTo]);

        if(!empty($fromDate) && !empty($toDate)) {
            $fromQuery->andWhere(['between', 'a.date_event', $fromDate, $toDate]);
            $statFromQuery->andWhere(['between', 'a.date_event', $fromDate, $toDate]);
        }
        if (!empty($adType)) {
            $fromQuery->andWhere(['a.type' => $adType]);
            $statFromQuery->andWhere(['a.type' => $adType]);
        }
        if (!empty($species)) {
            $fromQuery->andWhere(['a.id_species' => $species]);
            $statFromQuery->andWhere(['a.id_species' => $species]);
        }
        if (!empty($moderator)) {
            $fromQuery->andWhere(['a.id_specialist' => $moderator]);
            $statFromQuery->andWhere(['a.id_specialist' => $moderator]);
        }

        $query = (new Query)
            ->select([
                't.id',
                't.photo',
                't.ad_type',
                't.animal_name',
                't.species_name',
                't.breed_name',
                't.years_age',
                't.gender',
                't.colour',
                't.chip',
                't.date_event',
                't.time_event',
                't.fullname',
                't.phone',
                't.email',
                't.lost_found_address',
                't.notice',
                't.moderator',
                't.stamp',
                't.sso_id',
                't.service_number',
                't.verify_status',
                't.status',
                't.verify_at',
                't.created_at',
                't.updated_at',
                't.closed_at',
                't.closed_reason'
            ])
            ->from(['t' => $fromQuery])
            ->orderBy([
                't.id' => SORT_DESC,
                't.date_event' => SORT_DESC,
                't.time_event' => SORT_DESC]);

        $statQuery = (new Query)
            ->select([
                't.total_ads',
                't.total_moderating',
                't.total_approved',
                't.total_rej_mod',
                't.total_archived',
                't.total_autocensored',
                't.status',
            ])
            ->from(['t' => $statFromQuery]);

        $subtotal = [
            'total_ads' => 0,
            'total_moderating' => 0,
            'total_approved' => 0,
            'total_rej_mod' => 0,
            'total_archived' => 0,
            'total_autocensored' => 0
        ];

        if (!empty($adStatus)) {
            //Костыль. Сейчас в архивных показываем "закрыто модератором" и "закрыто автоматически"
            if (in_array('5', $adStatus)){
                if (!in_array('4', $adStatus)) array_push($adStatus, '4');
                if (!in_array('6', $adStatus)) array_push($adStatus, '6');
            }
            $query->andWhere(['t.status' => $adStatus]);
            foreach ($adStatus as $status) {
                foreach ($statQuery->all() as $value) {
                    if ($value['status'] == $status) {
                        $subtotal['total_ads'] += $value['total_ads'];
                        $subtotal['total_moderating'] += $value['total_moderating'];
                        $subtotal['total_approved'] += $value['total_approved'];
                        $subtotal['total_rej_mod'] += $value['total_rej_mod'];
                        $subtotal['total_archived'] += $value['total_archived'];
                        $subtotal['total_autocensored'] += $value['total_autocensored'];
                    }
                }
            }
//            $statQuery->andWhere(['t.status' => $adStatus]);
        } else {
            foreach ($statQuery->all() as $value) {
                $subtotal['total_ads'] += $value['total_ads'];
                $subtotal['total_moderating'] += $value['total_moderating'];
                $subtotal['total_approved'] += $value['total_approved'];
                $subtotal['total_rej_mod'] += $value['total_rej_mod'];
                $subtotal['total_archived'] += $value['total_archived'];
                $subtotal['total_autocensored'] += $value['total_autocensored'];
            }
        }

        $returnData = [
            'query' => $query,
            'subtotal' => $subtotal
        ];
        return $returnData;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = $this->buildQuery($this->fromDate, $this->toDate, $this->adType, $this->species, $this->adStatus, $this->createdFrom, $this->createdTo)['query'];
        $subtotal = $this->buildQuery($this->fromDate, $this->toDate, $this->adType, $this->species, $this->adStatus, $this->createdFrom, $this->createdTo)['subtotal'];

        $limit = 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $limit,
                'pageSizeLimit' => false,
            ],
        ]);

        $rows = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        return $this->render('index', [
            'rows' => $rows,
            'pagination' => $pagination,
            'countAds' => $this->countAds(),
            'moderatedAds' => $this->countModeratedAds(),
            'approvedAds' => $this->countApprovedAds(),
            'closedAds' => $this->countClosedAds(),
            'archivedAds' => $this->countArchivedAds(),
            'autoClosedAds' => $this->countAutoClosedAds(),
            'adType' => ['L' => 'Потеряно', 'F' => 'Найдено'],
            'species' => $this->catDogOptions(),
            'adStatus' => [
//                '1' => 'Активные',
                '2' => 'На модерации',
                '3' => 'Одобрено модератором',
                '4' => 'Закрыто модератором',
                '5' => 'Архивные',
                '6' => 'Автоматически отклоненные',
            ],
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'createdFrom' => $this->createdFrom,
            'createdTo' => $this->createdTo,
            'moderator' => $this->specialistsOptions(),
            'subtotal' => $subtotal
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $query = $this->buildQuery($this->from, $this->to, $this->organizations, $this->types, $this->services, $this->specialists, $this->channels);
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
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionBiExport()
    {
        /** @var Api $api */
        $api = $this->module->get('biApi');
        /** @var SearchPetsReportRq $rs */
        $rs = $api->sent(new SearchPetsReportRq([
            'from' => $this->fromDate,
            'to' => $this->toDate,
            'createdFrom' => $this->createdFrom,
            'createdTo' => $this->createdTo,
            'type' => $this->adType,
            'species' => $this->species,
            'status' => $this->adStatus,
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

    /**
     * @return int|string
     */
    public function countAds()
    {
        return Ad::find()
            ->count();
    }

    /**
     * @return int|string
     */
    public function countModeratedAds()
    {
        return Ad::find()
            ->andWhere(['is_active' => true])
            ->andWhere(['verify_status' => null])
            ->count();
    }

    /**
     * @return int|string
     */
    public function countApprovedAds()
    {
        return Ad::find()
            ->andWhere(['is_active' => true])
            ->andWhere(['verify_status' => true])
            ->count();
    }

    /**
     * @return int|string
     */
    public function countClosedAds()
    {
        return Ad::find()
            ->andWhere(['verify_status' => false])
            ->andWhere(['is_active' => false])
            ->andWhere(['closed_by' => 'MODERATOR'])
            ->count();
    }

    /**
     * @return int|string
     */
    public function countArchivedAds()
    {
        return Ad::find()
            ->andWhere(['is_active' => false])
            ->count();
    }

    /**
     * @return int|string
     */
    public function countAutoClosedAds()
    {
        return Ad::find()
            ->andWhere(['is_active' => false])
            ->andWhere(['=', 'closed_by', 'AUTO_CENSOR'])
            ->count();
    }

    /**
     * @param string $uid
     * @return string|null
     */
    private function prepareImage($uid)
    {
        $module = \Yii::$app->getModule('foundPet');
        $env = ArrayHelper::getValue($module->params, 'mosru_env');
        $serviceUrl = ArrayHelper::getValue($module->params, ['mosru_urls', $env, 'file_service_url']);
        if (!$serviceUrl) {
            return null;
        }
        $path = strtr($serviceUrl, ['{uid}' => $uid]);

        try {
            $contents = file_get_contents($path);
            if (empty($contents)) {
                return null;
            }
            $finfo = new \finfo(FILEINFO_MIME);
            $mime = $finfo->buffer($contents);
            if (empty($mime)) {
                return null;
            }
            $arr = explode(';', $mime);
            $mimeType = $arr[0];
            $url = 'data: ' . $mimeType . ';base64,' . base64_encode($contents);
        } catch (\Throwable $e) {
            return null;
        }

        return $url;
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShowPhoto($id)
    {
        $query = Ad::findOne(['id' => $id]);
        $photos = $query->photo;
        foreach ($photos as $key => $photoTxt) {
            if (!empty($photoTxt['id']) && $photoTxt['is_main']) {
                $photo = $this->prepareImage($photoTxt['id']);
                return $this->render('show-photo', [
                    'model' => $query,
                    'photo' => $photo,
                ]);
            }
        }
    }
}
