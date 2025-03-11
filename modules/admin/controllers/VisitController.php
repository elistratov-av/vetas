<?php

namespace app\modules\admin\controllers;

use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\modules\admin\models\Visits;
use app\modules\admin\models\search\VisitSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class VisitController
 * @package app\modules\admin\controllers
 */
class VisitController extends AdminController
{
    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new VisitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInfo($id)
    {
        $model = Visits::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Прием не найден');
        }

        return $this->render('info', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $visit = Visits::findOne(['id' => $id]);
        $visitSpecialist = VisitsSpecialists::findOne(['id_visit' => $id]);
        $visitServices = VisitsGovServices::findOne(['id_visit' => $id]);
        try {
            $visitServices->delete();
            $visitSpecialist->delete();
            $visit->delete();
            \Yii::$app->session->addFlash('info', 'Прием успешно удален');
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('danger', 'Не удалось удалить прием, обратитесь к администратору');
        }
        return $this->redirect(['index']);
    }
}
