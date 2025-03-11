<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.05.19
 * Time: 15:28
 */

namespace app\modules\admin\controllers\statistics;

use app\modules\admin\controllers\AdminController;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\helpers\ArraySum;
use app\modules\admin\models\AmbulanceReport;
use app\modules\admin\models\export\AmbulanceReportExport;

/**
 * Class AmbulanceDutyReportController
 * @package app\modules\admin\controllers\statistics
 */
class AmbulanceDutyReportController extends AdminController
{
    /**
     * @param $from
     * @param $to
     * @return \yii\db\ActiveQuery
     */
    protected function buildQuery($from, $to)
    {
        $query = AmbulanceReport::find()
            ->select([
                'id_specialist',
                'spec_name',
                'total_calls' => 'sum(total_calls)',
                'total_cancelled' => 'sum(total_cancelled)',
                'cancelled_by_owner' => 'sum(cancelled_by_owner)',
                'cancelled_by_org' => 'sum(cancelled_by_org)',
                'total_house_calls' => 'sum(total_house_calls)',
                'total_commercial' => 'sum(total_commercial)',
                'total_free_for_blind' => 'sum(total_free_for_blind)',
                'total_free_for_the_rest' => 'sum(total_free_for_the_rest)'
            ])
            ->groupBy(['id_specialist', 'spec_name'])
            ->andWhere(['>', 'total_calls', 0])
            ->orderBy(['spec_name' => SORT_ASC]);

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
        return AmbulanceReport::find()
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
        return AmbulanceReport::find()
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
        $from = \Yii::$app->request->get('from');
        $to = \Yii::$app->request->get('to');
        if (empty($from)) {
            $from = date('Y') . '-01-01';
        }

        if (empty($to)) {
            $to = date('Y-m-d');
        }

        $dataProvider = new AdminDataProvider([
            'query' => $this->buildQuery($from, $to),
            'pagination' => false
        ]);

        $dataProvider->prepare();
        $totalStatistic = ArraySum::getSum($dataProvider->getModels());
        if (!$totalStatistic) {
            $totalStatistic = 0;
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'totalStatistic' => $totalStatistic
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {
        $from = \Yii::$app->request->get('from');
        $to = \Yii::$app->request->get('to');
        if (empty($from)) {
            $from = date('Y') . '-01-01';
        }

        if (empty($to)) {
            $to = date('Y-m-d');
        }

        $mainQuery = $this->buildQuery($from, $to)->asArray()->all();
        $exportedReport = new AmbulanceReportExport();
        $exportedReport->export($mainQuery, "Контроль выездной службы c {$from} по {$to}.xls", $from, $to);
    }
}