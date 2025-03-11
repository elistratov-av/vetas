<?php

namespace app\modules\admin\controllers;

use app\models\db\ServiceTypes;
use app\modules\admin\models\GovServices;
use app\modules\admin\models\search\LogSearch;
use app\modules\admin\models\forms\GovServicesForm;
use app\modules\admin\models\search\MessageSearch;
use app\modules\admin\models\Visits;
use app\modules\soap\models\ServiceGoal;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Class MosRuController
 * @package app\modules\admin\controllers
 */
class MosRuController extends AdminController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = GovServices::find()->where(['type' => 'mosru'])->orderBy(['name' => SORT_ASC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMessages()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('messages', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionVisitData()
    {
        $visit_id = \Yii::$app->request->get('visit_id');
        $visit = Visits::find()->where(['id' => $visit_id])->one();
        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('visit-data', [
                'visit' => $visit,
            ]);
        } else {
            return $this->render('visit-data', [
                'visit' => $visit,
            ]);
        }
    }


    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogs()
    {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('logs', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionEdit()
    {
        $service_type = ServiceTypes::find()->select('name')->orderBy(['name' => SORT_ASC])->indexBy('id')->asArray()->column();
        $service_goal = ServiceGoal::find()->select('name')->orderBy(['name' => SORT_ASC])->indexBy('id')->asArray()->column();

        if (!$service = GovServices::findOne(Yii::$app->request->get('id'))) {
            throw new NotFoundHttpException('Услуга не найдена');
        }

        $model = new GovServicesForm();
        $model->setAttributes($service->getAttributes());
        $request = Yii::$app->request;
        if ($request->isPost && $model->load($request->post()) && $model->editService($service)) {
            return $this->redirect('/admin/mos-ru');
        } else {
            return $this->render('edit', [
                'model' => $model,
                'service_type' => $service_type,
                'service_goal' => $service_goal
            ]);
        }
    }


    /**
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionAdd()
    {
        $service_type = ServiceTypes::find()->select('name')->orderBy(['name' => SORT_ASC])->indexBy('id')->asArray()->column();
        $service_goal = ServiceGoal::find()->select('name')->orderBy(['name' => SORT_ASC])->indexBy('id')->asArray()->column();

        $model = new GovServicesForm();
        $request = Yii::$app->request;

        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            return $this->redirect('/admin/mos-ru');
        } else {
            return $this->render('add', [
                'model' => $model,
                'service_type' => $service_type,
                'service_goal' => $service_goal
            ]);
        }
    }

}
