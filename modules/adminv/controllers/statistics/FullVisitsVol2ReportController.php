<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.07.19
 * Time: 12:49
 */

namespace app\modules\adminv\controllers\statistics;

use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\Visits;
use app\modules\adminv\models\export\FullVisitsVol2ReportExport;
use app\modules\adminv\models\statistic\FullVisitsVol2Report;

/**
 * Детальный отчет по приемам
 *
 * Class FullVisitsVol2ReportController
 * @package app\modules\adminv\controllers\statistics
 */
class FullVisitsVol2ReportController extends StaticticsController
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
     * @var array
     */
    public $species;

    /**
     * @var array
     */
    public $visit_types;

    /**
     * @return string
     */
    public function actionIndex() {

        $organizationsList = $this->organizationsOptionsNoShelters();
        $areasList = $this->areasOptions();
        $districtsList = $this->districtsOptions();

        $speciesList = [
            'Кошки' => 'Кошки',
            'Собаки' => 'Собаки',
            'Иные животные' => 'Иные животные'
        ];

        $searchModel = $this->initSearchModel();
        $dataProvider = $searchModel->search()['dataProvider'];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'organizationsList' => $organizationsList,
            'areasList' => $areasList,
            'districtsList' => $districtsList,
            'speciesList' => $speciesList,
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
    public function actionExport() {

        $model = $this->initSearchModel();
        $data = $model->search()['data'];
        $from = $model->search()['from'];
        $to = $model->search()['to'];
        // $exportedReport = new FullVisitsVol2ReportExport();
        // $exportedReport->export($data, "Детальный отчет по приемам c {$from} по {$to}.xls", $from, $to);

        $exporter = new \app\modules\adminv\models\excel\FullVisitsVol2ReportExport([
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
        $this->districts = \Yii::$app->request->get('id_district', []);
        $this->species = \Yii::$app->request->get('spec_name', []);
        $this->visit_types = \Yii::$app->request->get('visit_types', Visits::types());
    }

    /**
     * @return \app\modules\adminv\models\statistic\FullVisitsVol2Report
     */
    private function initSearchModel()
    {
        $model = new FullVisitsVol2Report([
            'from' => $this->from,
            'to' => $this->to,
            'organizations' => $this->organizations,
            'areas' => $this->areas,
            'districts' => $this->districts,
            'species' => $this->species,
            //'visit_types' => $this->visit_types,
        ]);

        return $model;
    }
}
