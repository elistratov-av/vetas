<?php

namespace app\modules\adminv\controllers;

use app\modules\adminv\models\search\FoundPetAdSearch;
use app\modules\adminv\models\search\FoundPetMessageSearch;
use app\modules\adminv\models\search\FoundPetMessageSentSearch;

/**
 * Class FoundPetController
 * @package app\modules\adminv\controllers
 */
class FoundPetController extends AdminController
{
    /**
     * @return string|\yii\web\Response
     */
    public function actionMessages()
    {
        $searchModel = new FoundPetMessageSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams());

        return $this->render('messages', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionAds()
    {
        $searchModel = new FoundPetAdSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams());

        return $this->render('ads', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionMessagesSent()
    {
        $searchModel = new FoundPetMessageSentSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams());

        return $this->render('messages-sent', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
}
