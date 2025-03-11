<?php


namespace app\modules\adminv\controllers\statistics;


use app\models\db\Species;
use app\modules\adminv\models\export\NotificationsReportExport;
use app\modules\adminv\models\statistic\NotificationsReport;
use Yii;
use yii\db\Query;

class NotificationsReportController extends StaticticsController
{
    public $species;
    public $owners;
    public $senders;

    /**
     * @return string
     */
    public function actionIndex() {

        $speciesList = $this->speciesOptions();
        $sendersList = ['system' => 'Система', 'inspector' => 'Госветинспектор'];
        $searchModel = $this->initSearchModel();
        $dataProvider = $searchModel->search()['dataProvider'];
        $ownersOptions = $searchModel->search()['ownersOptions'];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
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
        $exportedReport = new NotificationsReportExport();
        $exportedReport->export($data, "Svodnyi otchet po uvedomleniyam s {$this->from} po {$this->to}.xlsx", $this->from, $this->to);
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
     * @return NotificationsReport
     */
    private function initSearchModel()
    {
        $model = new NotificationsReport([
            'from' => $this->from,
            'to' => $this->to,
            'species' => $this->species,
            'owners' => $this->owners,
            'senders' => $this->senders
        ]);

        return $model;
    }
}