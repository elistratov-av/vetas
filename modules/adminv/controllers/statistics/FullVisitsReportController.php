<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.06.19
 * Time: 15:48
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\ShiftType;
use app\models\db\Visits;
use app\modules\admin\models\Organization;
use app\modules\adminv\models\export\FullVisitsReportExport;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Общий отчет по приемам
 *
 * Class FullVisitsReportController
 * @package app\modules\adminv\controllers\statistics
 */
class FullVisitsReportController extends StaticticsController
{
    public $areas;
    public $districts;
    public $channels;
    public $visit_types;
    public $params;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->districts = \Yii::$app->request->get('id_district', []);
        $this->visit_types = \Yii::$app->request->get('visit_types', Visits::types());

        $shift_types = [
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_SHELTER,
            ShiftType::ASSIGN_SHIFT_TYPE_FOR_DETOUR,
        ];

        $channels = ShiftType::find()
            ->select(['id', 'type'])
            ->where(['type' => $shift_types])
            ->asArray()
            ->all();

        // type => id
        $channels = ArrayHelper::map($channels, 'type', 'id');
        $this->channels = $channels;

        if (empty($this->from)) {
            $this->from = date("Y") - 5 . '-01-01';
        }
        if (empty($this->to)) {
            $this->to = date('Y-m-d');
        }

        $this->params = array_merge([
            ':cancelled' => VisitStatus::CANCELED,
            ':transfered' => VisitStatus::TRANSFER,
            ':finished' => VisitStatus::FINISHED,
        ]);
    }

    /**
     * @param $params
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($params)
    {
        $mainQuery = Organization::find()
            ->select([
                'organizations.id as id_organization',
                'organizations.short_name',
                'organizations.id_fias_address',
                'fias_addresses.id_area',
                'areas.name as area_name',
                'fias_addresses.id_district',
                'districts.name as dist_name']);

        foreach ($this->channels as $type => $id_channel){
            $mainQuery->addSelect([
                new Expression("sum(case when visits.channel = $id_channel then 1 else 0 end) as {$type}_created", $params),
                new Expression("sum(case when visits.channel = $id_channel and visits.status = :transfered then 1 else 0 end) as {$type}_transfered", $params),
                new Expression("sum(case when visits.channel = $id_channel and visits.status = :cancelled then 1 else 0 end) as {$type}_cancelled", $params),
                new Expression("sum(case when visits.channel = $id_channel and visits.status = :finished then 1 else 0 end) as {$type}_finished", $params),
            ]);
        }

        $mainQuery
            ->joinWith([
                'fias_addresses',
                'fias_addresses.area',
                'fias_addresses.district',
                'visits',
            ], false)
            ->andWhere(['between', 'coalesce(visits.fact_start_dttm, visits.start_dttm, visits.created_at)::date', $this->from, $this->to])
            //->andWhere(['visits.type' => $this->visit_types])
            ->groupBy([
                'organizations.id',
                'fias_addresses.id_area',
                'area_name',
                'fias_addresses.id_district',
                'dist_name',
                'organizations.short_name',
                'organizations.id_fias_address'
            ])
            ->orderBy([
                'area_name' => SORT_ASC,
                'dist_name' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
            ])
            ->asArray();

        if (!empty($this->organizations)) {
            $mainQuery->andWhere(['organizations.id' => $this->organizations]);
        }
        if (!empty($this->areas)) {
            $mainQuery->andWhere(['fias_addresses.id_area' => $this->areas]);
        }
        if (!empty($this->districts)) {
            $mainQuery->andWhere(['fias_addresses.id_district' => $this->districts]);
        }

        $data = $mainQuery->all();

        return $data;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->params);

        return $this->render('index', [
            'data' => $data,
            'organizations' => $this->organizationsOptionsNoShelters(),
            //'visit_types' => $this->visitTypesOptions(),
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
        $data = $this->buildQuery($this->params);

        $exporter = new FullVisitsReportExport();
        $exporter->export($data, "Общий отчет по приемам c {$this->from} по {$this->to}.xlsx", $this->from, $this->to);
    }
}
