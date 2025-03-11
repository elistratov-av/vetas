<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 01.03.19
 * Time: 15:48
 */

namespace app\modules\adminv\controllers\statistics;

use app\common\models\VisitStatus;
use app\models\db\Organizations;
use yii\db\Expression;

/**
 * Краткая статистика организаций по регистрации и приемам
 *
 * Class CommonReportController
 * @package app\modules\adminv\controllers\statistics
 */
class CommonReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $areas;

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
    }

    /**
     * @param string $from
     * @param string $to
     * @param array  $organizations
     * @param array  $areas
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     */
    private function buildQuery($from, $to, $organizations, $areas)
    {
        $mainQuery = Organizations::find()
            ->alias('o')
            ->select([
                'row_number() over (order by a.name, o.short_name) as number',
                'o.id as id',
                'o.short_name',
                'fa.id_area',
                'a.name',
                '(select count(*) from pets p where p.reg_date is not null and p.id_reg_organization = o.id) as total_pets',
                new Expression('(select count(*) from pets p where p.reg_date is not null and p.reg_date between :from and :to and p.id_reg_organization = o.id) as period_pets', [':from' => $from, ':to' => $to]),
                new Expression('(select count(*) from visits v where v.status = :finished and v.id_organization = o.id) as total_visits', [':finished' => VisitStatus::FINISHED]),
                new Expression('(select count(*) from visits v where v.status = :finished and coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::date between :from and :to and v.id_organization = o.id) as period_visits', [':finished' => VisitStatus::FINISHED, ':from' => $from, ':to' => $to]),
            ])
            ->leftJoin('fias_addresses fa', 'fa.id = o.id_fias_address')
            ->leftjoin('areas a', ['a.id' => new Expression('fa.id_area')])
            ->innerJoin('org_types ot', 'ot.id = o.id_org_type and ot.is_tech = false')
            ->groupBy(['o.id', 'o.short_name', 'a.name', 'fa.id_area'])
            ->orderBy([
                'a.name' => SORT_ASC,
                'o.short_name' => SORT_ASC,
            ]);


        if (!empty($organizations)) {
            $mainQuery->andWhere(['o.id' => $organizations]);
        }
        if (!empty($areas)) {
            $mainQuery->andWhere(['fa.id_area' => $areas]);
        }

        $data = $mainQuery->asArray()->all();

        return $data;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas);

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
        $data = $this->buildQuery($this->from, $this->to, $this->organizations, $this->areas);
        // $exportedReport = new CommonReportExport();
        // $exportedReport->export($data, "Краткая статистика организаций c {$this->from} по {$this->to}.xls", $this->from, $this->to);

        $exporter = new \app\modules\adminv\models\excel\CommonReportExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $exporter->export();
    }
}
