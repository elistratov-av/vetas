<?php

namespace app\modules\soap\controllers;

class CallbackController extends BaseController
{
    public function actionIndex()
    {
        throw new \yii\web\BadRequestHttpException();
        \Yii::$app->getModule('soap')->etp->handleRequest(\Yii::$app->request->rawBody);
    }

}
