<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class AppController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
    }
}