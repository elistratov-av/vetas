<?php

namespace app\modules\adminv\controllers\statistics;

use app\modules\adminv\models\export\VaccinationReportExport;
use app\modules\adminv\models\statistic\VaccinationReport;
use yii\helpers\ArrayHelper;

/**
 * Отчет по охвату вакцинацией против бешенства
 *
 * Class VaccinationReportController
 * @package app\modules\adminv\controllers\statistics
 */
class VaccinationReportController extends StaticticsController
{
    /**
     * @var array
     */
    public $areas;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = $this->initSearchModel();
        $data = ArrayHelper::getValue($searchModel->search(), 'data', []);

        return $this->render('index', [
            'data' => $data,
            'searchModel' => $searchModel,
            'organizationsList' => $this->organizationsOptionsNoShelters(),
            'areasList' => $this->areasOptions(),
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
        $data = ArrayHelper::getValue($model->search(), 'data', []);
        $from = $model->search()['from'];
        $to = $model->search()['to'];
        // $exportedReport = new VaccinationReportExport();
        // $exportedReport->export($data, "Отчет по охвату вакцинацией против бешенства c {$from} по {$to}.xlsx", $from, $to);

        $exporter = new \app\modules\adminv\models\excel\VaccinationReportExport([
            'data' => $data,
            'from' => $from,
            'to' => $to,
        ]);

        $exporter->export();
    }

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->areas = \Yii::$app->request->get('id_area', []);
    }

    /**
     * @return \app\modules\adminv\models\statistic\VaccinationReport
     */
    private function initSearchModel()
    {
        $model = new VaccinationReport([
            'from' => $this->from,
            'to' => $this->to,
            'organizations' => $this->organizations,
            'areas' => $this->areas,
        ]);

        return $model;
    }
}
