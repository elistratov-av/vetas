<?php

namespace app\modules\admin\controllers\statistics;

use app\modules\admin\controllers\AdminController;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\export\MosruReportExport;
use app\modules\admin\models\MosruOrganizationsStat;
use yii\db\ActiveQuery;

/**
 * Class MosRuController
 * @package app\modules\admin\controllers\statistics
 */
class MosRuController extends AdminController
{
    public $from;
    public $to;

    /**
     *
     */
    public function init()
    {
        $this->from = \Yii::$app->request->get('from');
        $this->to = \Yii::$app->request->get('to');
        if (empty($this->from)) {
            $this->from = '2018-12-01'; // дата начала работы записи с mos.ru в проде
        }

        if (empty($this->to)) {
            $this->to = date('Y-m-d');
        }
        parent::init(); 
    }

    /**
     * @param $from
     * @param $to
     * @return ActiveQuery
     */
    protected function buildQuery($from, $to)
    {
        /** @var ActiveQuery $query */
        $query = MosruOrganizationsStat::find()
            ->select([
                'id_organization',
                'total' => 'sum(total)',
                'moved' => 'sum(moved)',
                'canceled_by_owner' => 'sum(canceled_by_owner)',
                'canceled_by_org' => 'sum(canceled_by_org)',
                'cats_visits' => 'sum(cats_visits)',
                'dogs_visits' => 'sum(dogs_visits)',
                'other_visits' => 'sum(other_visits)',
                'cats_finished_visits' => 'sum(cats_finished_visits)',
                'dogs_finished_visits' => 'sum(dogs_finished_visits)',
                'other_finished_visits' => 'sum(other_finished_visits)'
            ])
            ->joinWith(['organization'])
            ->groupBy(['id_organization', 'organizations.short_name'])
            ->orderBy(['organizations.short_name' => SORT_ASC]);

        if (!empty($from)) {
            $query->andWhere(['>=', 'date', $from]);
        }

        if (!empty($to)) {
            $query->andWhere(['<=', 'date', $to]);
        }

        return $query;
    }

    /**
     * @param $date
     * @return false|string|null
     */
    protected function getPrevDate($date)
    {
        return MosruOrganizationsStat::find()
            ->select('date')
            ->distinct()
            ->where(['<', 'date', $date])
            ->orderBy(['date' => SORT_DESC])
            ->limit(1)
            ->scalar();
    }

    /**
     * @param $date
     * @return false|string|null
     */
    protected function getNextDate($date)
    {
        return MosruOrganizationsStat::find()
            ->select('date')
            ->distinct()
            ->where(['>', 'date', $date])
            ->orderBy(['date' => SORT_ASC])
            ->limit(1)
            ->scalar();
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new AdminDataProvider([
            'query' => $this->buildQuery($this->from, $this->to),
            'pagination' => false
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $mainQuery = $this->buildQuery($this->from, $this->to)->asArray()->all();
        $exportedReport = new MosruReportExport();
        $exportedReport->export($mainQuery, "Статистика mos.ru c {$this->from} по {$this->to}.xlsx", $this->from, $this->to);
    }
}