<?php


namespace app\modules\foundPet\controllers;


use yii\web\Controller;

class TestController extends Controller
{
    /**
     * "Отбойник" для тестированяи отправки статусов
     *  found-pet/test/test
     * @return string[]
     */
    public function actionTest()
    {
        return ['result' => 'test'];
    }
}