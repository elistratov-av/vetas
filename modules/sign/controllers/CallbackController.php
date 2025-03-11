<?php

namespace app\modules\sign\controllers;

use yii\web\Response;
use yii\base\Controller;
use Yii;

class CallbackController extends Controller
{
    /**
     * Принимаем и записываем в лог
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $log = [
            'body' => $request->rawBody,
            'headers' => $request->headers->toArray()
        ];
        Yii::info($log, 'sign');
    }
    
    /**
     * Вывод лога
     */
    public function actionList()
    {   
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $log = Yii::getAlias('@app') . '/runtime/logs/sign.log';
        if (file_exists($log)) {
            return '<pre>' . file_get_contents($log) . '</pre>';
        }
    }
}
