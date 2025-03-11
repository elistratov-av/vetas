<?php

namespace app\modules\adminv\controllers\statistics;

use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\MosruOrganizationsStat;
use app\modules\adminv\models\export\MosruReportExport;
use yii\db\ActiveQuery;

/**
 * Статистика mos.ru
 *
 * Class MosRuController
 * @package app\modules\adminv\controllers\statistics
 */
class MosRuController extends StaticticsController
{
    /**
     * @param $from
     * @param $to
     * @return ActiveQuery
     */
    private function buildQuery($from, $to)
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
                'other_finished_visits' => 'sum(other_finished_visits)',
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

        if (!empty($this->organizations)) {
            $query->andWhere(['id_organization' => $this->organizations]);
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
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
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
        $data = $this->buildQuery($this->from, $this->to)
            ->asArray()
            ->all();
        $organizations = $this->organizationsOptionsNoShelters();
        // $exportedReport = new MosruReportExport();
        // $exportedReport->export($data, "Статистика mos.ru c {$this->from} по {$this->to}.xlsx", $this->from, $this->to, $organizations);

        $exporter = new \app\modules\adminv\models\excel\MosRuExport([
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
            'organizations' => $organizations,
        ]);

        $exporter->export();
    }
}
