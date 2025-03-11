<?php

namespace app\modules\v1\controllers;

use app\common\controllers\ApiController;
use app\modules\v1\models\FiasAddressResource;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

class FiasController extends ApiController
{
    protected $addrSrv;

    protected function verbs()
    {
        return ArrayHelper::merge(parent::verbs(), [
            '*' => ['GET'],
            'save' => ['POST'],
        ]);
    }

    public function actionIndex(){
        return 'fias';
    }

    /**
     * @param $action
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->addrSrv = \Yii::$app->addressService;
        return parent::beforeAction($action);
    }

    public function actionRegion()
    {
        $q = \Yii::$app->getRequest()->getQueryParam('q');
        $id = \Yii::$app->getRequest()->getQueryParam('id');
        return $this->addrSrv->getRegion($q, $id);
    }

    public function actionCity()
    {
        $q = \Yii::$app->getRequest()->getQueryParam('q');
        $region = \Yii::$app->getRequest()->getQueryParam('region');
        return $this->addrSrv->getCity($q, $region);
    }

    public function actionStreet()
    {
        $q = \Yii::$app->getRequest()->getQueryParam('q');
        $region = \Yii::$app->getRequest()->getQueryParam('region');
        $city = \Yii::$app->getRequest()->getQueryParam('city');
        return $this->addrSrv->getStreet($q, $region, $city);
    }

    public function actionHouse()
    {
        $q = \Yii::$app->getRequest()->getQueryParam('q');
        $street = \Yii::$app->getRequest()->getQueryParam('street');
        $city = \Yii::$app->getRequest()->getQueryParam('city');
        return $this->addrSrv->getHouse($q, $street, $city);
    }

    public function actionRoom()
    {
        $q = \Yii::$app->getRequest()->getQueryParam('q');
        $house = \Yii::$app->getRequest()->getQueryParam('house');
        return $this->addrSrv->getRoom($q, $house);
    }

    public function actionFull(){
        $id = \Yii::$app->getRequest()->getQueryParam('id');
        return $this->addrSrv->getFullAddress($id);
    }

}
