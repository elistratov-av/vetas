<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 31.08.20
 * Time: 11:49
 */

namespace app\modules\adminv\controllers\statistics;


use app\modules\adminv\models\export\EmailReportExport;
use app\modules\adminv\models\statistic\EmailReport;

class EmailReportController extends StaticticsController
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
     * @return string
     */
    public function actionIndex() {

        $areasList = $this->areasOptions();
        $districtsList = $this->districtsOptions();

        $searchModel = $this->initSearchModel();
        $dataProvider = $searchModel->search()['dataProvider'];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'areasList' => $areasList,
            'districtsList' => $districtsList,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport() {

        $model = $this->initSearchModel();
        $data = $model->search()['data'];
        $from = $model->search()['from'];
        $to = $model->search()['to'];
        $exportedReport = new EmailReportExport();
        $exportedReport->export($data, "Отчет по владельцам с электронной почтой c {$from} по {$to}.xlsx", $from, $to);
    }

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
        $this->districts = \Yii::$app->request->get('id_district', []);
    }

    /**
     * @return EmailReport
     */
    private function initSearchModel()
    {
        $model = new EmailReport([
            'from' => $this->from,
            'to' => $this->to,
            'areas' => $this->areas,
            'districts' => $this->districts,
        ]);

        return $model;
    }
}