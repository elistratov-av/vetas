<?php


namespace app\modules\adminv\controllers\statistics;


use app\modules\adminv\integration\bi\Api;
use app\modules\adminv\integration\bi\messages\UnvaccPetsReportRq;
use app\modules\adminv\integration\bi\messages\UnvaccPetsReportRs;
use app\modules\adminv\models\statistic\UnvaccPetsReport;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class UnvaccPetsReportController extends StaticticsController
{

    /**
     * @var array
     */
    public $bti_city_area_codes;

    /**
     * @return string
     */
    public function actionIndex()
    {

        $btiCityAreaCodeOptions = $this->btiCityAreaCodeOptions();
        $btiCityAreaCodeOptions['Без округа/района'] = ['without_bti' => 'Указывать'];

        $searchModel = $this->initSearchModel();
        $dataProvider = $searchModel->search()['dataProvider'];
        $mainQuery = $searchModel->search()['mainQuery'];

        $rows = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();
        $subtotals = empty($rows) ? [] : $searchModel->calculateSubtotals();

        return $this->render('index', [
            'rows' => $rows,
            'pagination' => $pagination,
            'subtotals' => $subtotals,
            'mainQuery' => $mainQuery,

            'btiCityAreaCodeOptions' => $btiCityAreaCodeOptions,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport()
    {

        $model = $this->initSearchModel();
        $date = date('d.m.Y');
        $data = $model->search()['data'];
        $exporter = new \app\modules\adminv\models\excel\UnvaccPetsReportExport([
            'data' => $data,
            'to' => $date,
        ]);

        $exporter->export();
    }

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

       $this->bti_city_area_codes = \Yii::$app->request->get('bti_city_area_code', []);
    }

    /**
     * @return UnvaccPetsReport
     */
    private function initSearchModel()
    {
        $model = new UnvaccPetsReport([
            'bti_city_area_codes' => $this->bti_city_area_codes,
        ]);

        return $model;
    }

    //ToDo area/disctrict to bti_area_code
    public function actionBiExport()
    {
        /** @var Api $api */
        $api = $this->module->get('biApi');
        /** @var UnvaccPetsReportRs $rs */
        $rs = $api->sent(new UnvaccPetsReportRq([
            'area' => [3],
            'district' => [],
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
}