<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.07.19
 * Time: 12:49
 */

namespace app\modules\admin\controllers\statistics;

use app\models\db\Areas;
use app\models\db\Districts;
use app\modules\admin\controllers\AdminController;
use app\modules\admin\models\export\FullVisitsVol2ReportExport;
use app\modules\admin\models\Organization;
use app\modules\admin\models\statistics\FullVisitsVol2Report;

/**
 * Class FullVisitsVol2ReportController
 * @package app\modules\admin\controllers\statistics
 */
class FullVisitsVol2ReportController extends AdminController
{
    /**
     * @return string
     */
    public function actionIndex() {

        $organizationsList = Organization::find()
            ->select('short_name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
        $areasList = Areas::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
        $districtsList = Districts::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();

        $speciesList = [
            'Кошки' => 'Кошки',
            'Собаки' => 'Собаки',
            'Иные животные' => 'Иные животные'
        ];

        $searchModel = new FullVisitsVol2Report();
        $dataProvider = $searchModel->search()['dataProvider'];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'organizationsList' => $organizationsList,
            'areasList' => $areasList,
            'districtsList' => $districtsList,
            'speciesList' => $speciesList,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport() {

        $model = new FullVisitsVol2Report();
        $mainQuery = $model->search()['data'];
        $from = $model->search()['from'];
        $to = $model->search()['to'];
        $exportedReport = new FullVisitsVol2ReportExport();
        $exportedReport->export($mainQuery, "Детальный отчет по приемам c {$from} по {$to}.xls", $from, $to);
    }
}