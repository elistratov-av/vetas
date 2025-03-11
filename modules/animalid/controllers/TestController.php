<?php

namespace app\modules\animalid\controllers;

use app\modules\animalid\models\DataConsumer;
use yii\web\Controller;

class TestController extends Controller
{
    /**
     * http://192.168.56.204:9710/animalid/test
     */
    public function actionIndex()
    {
        $request = \Yii::$app->getRequest();
        $msg = $request->getRawBody();

        $integrator = new DataConsumer();
        $res = $integrator->consume($msg);
        return ['etst'];
    }
}
