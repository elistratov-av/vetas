<?php


namespace app\modules\adminv\controllers\statistics;


use app\models\db\Species;
use app\modules\adminv\models\export\SearchEventsReportExport;
use app\modules\adminv\models\statistic\SearchEventsReport;
use Yii;
use yii\db\Query;

class SearchEventsReportController extends StaticticsController
{
    public $species;
    public $owners;
    public $senders;

    /**
     * @return string
     */
    public function actionIndex() {

        $speciesList = $this->catDogOptions();
        $sendersList = ['system' => 'Система', 'inspector' => 'Госветинспектор'];
        $searchModel = $this->initSearchModel();
        $data = $searchModel->search()['data'];
        $ownersOptions = $this->adAuthorsOptions();

        return $this->render('index', [
            'data' => $data,
            'searchModel' => $searchModel,
            'speciesList' => $speciesList,
            'ownersList' => $ownersOptions,
            'sendersList' => $sendersList,
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
        $exportedReport = new SearchEventsReportExport();
        $exportedReport->export($data, "Statistika po sobytiyam poiska s {$this->from} po {$this->to}.xlsx", $this->from, $this->to);
    }

    /**
     * @inheritDoc
     */
    protected function initVars()
    {
        parent::initVars();

        $this->species = Yii::$app->request->get('spec_name', []);
        $this->owners = Yii::$app->request->get('id_owner', []);
        $this->senders = Yii::$app->request->get('senders', []);
    }

    /**
     * @return SearchEventsReport
     */
    private function initSearchModel()
    {
        $model = new SearchEventsReport([
            'from' => $this->from,
            'to' => $this->to,
            'species' => $this->species,
            'owners' => $this->owners,
            'senders' => $this->senders
        ]);

        return $model;
    }
}